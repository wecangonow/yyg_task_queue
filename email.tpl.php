<?php

return $email_tpl =  [
    "country" => "malaysia", // one of malaysia turkey russia
    "subject" => "TURNAVI ÖDEME BİLGİLENDİRMESİ",
    "body" => '

<div id="Resume_payment" style="width:620px; height:auto; margin:0 auto; padding:0; font-family: Helvetica, Arial, sans-serif; padding-top:32px;">
	<div style="width:88px; height:88px; margin:0 auto;">
		<img src="https://s3.eu-central-1.amazonaws.com/frank-yyg-tur/data/img/170303/6f9c6b70f2e2ab7048bf0054f98ef337.png" _src="https://s3.eu-central-1.amazonaws.com/frank-yyg-tur/data/img/170303/6f9c6b70f2e2ab7048bf0054f98ef337.png" width="88" height="88">
	</div>
	<div style=" padding-bottom:10px; padding-top:15px;">
		<h2 style="font-family:Helvetica, Arial, sans-serif; font-size:18px; line-height:30px; font-weight:normal; text-align:center;">TURNAVI ÖDEME BİLGİLENDİRMESİ</h2>
	</div>
	<div style="padding:0 10px;color:#555555; font-family:Helvetica, Arial, sans-serif; font-size:16px;">
		<p style="margin:0;">
			Sevgili Kullanıcı,
			<br />
		</p>
		<p style=" line-height:26px;">
			Turnavı ödeme fanksiyonu tekrar aktif hale getirilmiştir, Turnavı Parası satın almak ve çekilişlere katılabilmek için ödeme yapabilirsiniz.
		</p>
		<p style=" line-height:26px;">
			Ödemeyi tekrar kullanabilmek için lütfen uygulamayı güncellemeyi unutmayın. Bu süreçte sabırla beklediğiniz için teşekkür ederiz.
		</p>
		<p style=" line-height:26px;">
			Bol Şans,
		</p>
	</div>
	<div style="margin:0 auto; border-radius:4px; background:#ED006F; width:200px; height:50px; line-height:50px; margin-top:25px;">
		<a target="_blank" href="http://1.turnavi.com/trace_url.php" style="display:block; text-decoration:none; font-family:Helvetica, Arial, sans-serif; font-size:16px; text-align:center; color:#ffffff;">HEMEN KAZAN</a>
	</div>
</div>

    ',
    "is_html" => true,
    "sender_info" => "TURNAVI"
];
