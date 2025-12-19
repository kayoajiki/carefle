<x-mail::message>
# メールアドレスの認証

以下のボタンをクリックして、メールアドレスを認証してください。

<x-mail::button :url="$url">
メールアドレスを認証
</x-mail::button>

もしアカウントを作成していない場合は、このメールを無視してください。

よろしくお願いいたします。<br>
{{ config('app.name') }}
</x-mail::message>
