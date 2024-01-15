# SimpleMail class 0.7.13

A simple mail composer, phpmailer alternative, SMTP client.  
UTF-8 html messages and attachements supported.

## Basic Usage
```php
$mail = new Shuchkin\SimpleMail();
$mail->setFrom('example@example.com')
	->setTo('sergey.shuchkin@gmail.com')
	->setSubject('Test SimpleMail')
	->setText('Hi, Sergey!')
	->send();
```
## Install

The recommended way to install this library is [through Composer](https://getcomposer.org).
[New to Composer?](https://getcomposer.org/doc/00-intro.md)

This will install the latest supported version:

```bash
$ composer require shuchkin/simplemail
```
or download class [here](https://github.com/shuchkin/simplemail/blob/master/src/SimpleMail.php)

## Fabric
```php
// setup mail
$mail = new Shuchkin\SimpleMail();
$mail->setFrom('example@example.com', 'Example');
 
// fabric method to( $toEmail )
$mail->to('sergey.shuchkin@gmail.com')
	->setSubject('Account activation')
	->setHTML('<html><body><p><b>Your account has activated!</b></p></body></html>', true)
	->send();

// fabric method compose( $toEmail, $subject, $text )	
$mail->compose('admin@example.com', 'New Account', 'https://example.com/useradmin/123')->send();
```
## SMTP
```php
$mail = new Shuchkin\SimpleMail('smtp', [
	'host' => 'ssl://smtp.yandex.ru',
    'port'     => 465,
    'username' => 'test@yandex.ru',
    'password' => 'test'
]);

$mail->setFrom('test@yandex.ru)
	->setTo('sergey.shuchkin@gmail.com')
	->setSubject('Test SMTP')
	->setText('Yandex SMTP secured server')
	->send();
```
## Attachments & reply
```php
$mail = new Shuchkin\SimpleMail();
$mail->setFrom('example@example.com')
	->setTo('sergey.shuchkin@gmail.com')
	->setSubject('Test attachments')
	->setHTML('<html><body><p>See attached price list.</p><p><img src="logo.jpg" /> Logo</p></body></html>')
	->attach( __DIR__.'/doc/PriceList.pdf')
	->attach( __DIR__.'/images/logo400x300.jpg', 'logo.jpg')
	->setReply('manager@example.com')
	->send();
```
## Priority & custom headers
```php
$mail = new Shuchkin\SimpleMail();
$mail->setFrom('example@example.com')
	->setTo('sergey.shuchkin@gmail.com')
	->setSubject('WARNING!')
	->setText('SERVER DOWN!')
	->setPriority('urgent')
	->setCustomHeaders(['Cc' => 'admin@exmple.com'])
	->send();
```
## Custom transport
```php
$mail = new Shuchkin\SimpleMail( function( $mail, $encoded ) {
	print_r( $encoded );	
});
$mail->setFrom('example@example.com')
	->setTo('sergey.shuchkin@gmail.com')
	->setSubject('WARNING!')
	->setText('SERVER DOWN!')
	->send();

/*
Array
(
    [from] => example@example.com
    [to] => sergey.shuchkin@gmail.com
    [subject] => =?UTF-8?B?V0FSTklORyE=?=
    [message] => SERVER DOWN!
    [headers] => To: sergey.shuchkin@gmail.com
Subject: =?UTF-8?B?V0FSTklORyE=?=
X-Mailer: PHP/7.2.14
MIME-Version: 1.0
From: example@example.com
Reply-To: example@example.com
Date: Mon, 18 Feb 2019 13:17:28 +0000
Content-Type: text/plain; charset="UTF-8"
)
*/
```
## Export & import
```
SimpleMail::toArray() - export to array
SimpleMail::fromArray( $data ) - import from assoc array (fabric)
SimpleMail::toJSON() - export to JSON
SimpleMail::fromJSON( $json ) - import from json (fabric)
```

## History
0.7.13 (2023-01-15) all properties is public now 
0.7.12 (2022-02-04) PHP/5.3 support  
0.7.11 (2019-02-18) Initial release 
