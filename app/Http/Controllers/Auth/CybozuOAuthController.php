<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OAuthToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CybozuOAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $subdomain = config('cybozu.subdomain');
        abort_if(! $subdomain, 500, 'CYBOZU_SUBDOMAIN is not set.');

        $state = Str::random(40);
        $request->session()->put('cybozu_oauth_state', $state);

        $authorizeUrl = (config('cybozu.authorize_url'))($subdomain);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id'     => config('cybozu.client_id'),
            'redirect_uri'  => config('cybozu.redirect_uri'),
            'scope'         => implode(' ', config('cybozu.scopes')),
            'state'         => $state,
        ]);

        return redirect()->away($authorizeUrl . '?' . $query);
    }

    public function callback(Request $request)
    {
        $state = $request->string('state')->toString();
        $expected = $request->session()->pull('cybozu_oauth_state');

        abort_if(! $expected || ! hash_equals($expected, $state), 419, 'Invalid state.');

        $code = $request->string('code')->toString();
        abort_if(! $code, 400, 'Authorization code not found.');

        $subdomain = config('cybozu.subdomain');
        $tokenUrl  = (config('cybozu.token_url'))($subdomain);

        // Token endpoint requires Authorization: Basic base64(client_id:client_secret) :contentReference[oaicite:2]{index=2}
        $basic = base64_encode(config('cybozu.client_id') . ':' . config('cybozu.client_secret'));

        $tokenResponse = Http::asForm()
            ->withHeaders(['Authorization' => 'Basic ' . $basic])
            ->post($tokenUrl, [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'redirect_uri' => config('cybozu.redirect_uri'),
            ]);

        if (! $tokenResponse->ok()) {
            abort(500, 'Token request failed: ' . $tokenResponse->body());
        }

        $token = $tokenResponse->json();
        $accessToken  = $token['access_token'] ?? null;
        $refreshToken = $token['refresh_token'] ?? null;
        $tokenType    = $token['token_type'] ?? null;
        $expiresIn    = $token['expires_in'] ?? null;
        $scope        = $token['scope'] ?? implode(' ', config('cybozu.scopes'));

        abort_if(! $accessToken, 500, 'access_token missing.');

        // ここが「kintoneユーザー情報取得」パート
        // cybozu/kintoneの環境によって取得エンドポイントが異なる/制約があるため
        // まずは「login_name をどう確実に得るか」をあなたの環境に合わせて確定します。
        //
        // ✅ いったん最小で：login_name を “一時的に” 固定値にして動作確認 → 次に取得実装へ
        //
        // ★あなたの環境で「OAuth後にユーザー情報を取得するAPI」(レスポンスにログイン名が含まれる) が確定したら差し替えます。
        $loginName = $this->resolveLoginName($request, $accessToken);

        // users: ULID主キー、login_nameはkintoneログイン名
        $user = User::firstOrCreate(
            ['login_name' => $loginName],
            [
                'name'     => $loginName, // 後で表示名に差し替え
                'email'    => null,       // kintone側から取得できるなら入れる
                'password' => bcrypt(Str::random(32)), // OAuth用ダミー
            ]
        );

        OAuthToken::updateOrCreate(
            ['user_id' => $user->id, 'provider' => 'cybozu'],
            [
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type'    => $tokenType,
                'scope'         => $scope,
                'expires_at'    => is_numeric($expiresIn) ? now()->addSeconds((int) $expiresIn) : null,
            ]
        );

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * まずは「動くところまで」確認するための暫定実装。
     * 次のステップで、実際にkintone/cybozu APIからlogin_nameを取得する実装に置き換えます。
     */
    private function resolveLoginName(Request $request, string $accessToken): string
    {
        // 暫定: 開発中は固定 or クエリ指定でもOK
        // 例: /auth/cybozu/callback?...&dev_login=sato.motoi
        $dev = $request->string('dev_login')->toString();
        if ($dev !== '') {
            return strtolower($dev);
        }

        // ひとまず“必ず”何か返す
        // 本番ではここを必ず「ユーザー情報取得」に置き換えます
        return 'unknown.user';
    }
}
