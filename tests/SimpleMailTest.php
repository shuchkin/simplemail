<?php /** @noinspection PhpLanguageLevelInspection */
/**
 * Created by PhpStorm.
 * User: Serg
 * Date: 14.02.2019
 * Time: 21:17
 */
require_once __DIR__.'/../src/SimpleMail.php';

use Shuchkin\SimpleMail;
use PHPUnit\Framework\TestCase;

class SimpleMailTest extends TestCase {

	public function testSetReply() {
		$m = new SimpleMail();
		$m->setReplyTo('sergey.shuchkin@gmail.com', 'Sergey');
		self::assertEquals( 'Sergey', $m->getReplyToName());
	}

	public function testGetTransportParams() {
		$m = new SimpleMail('smtp', [ 'username' => 'test'] );
		$tp = $m->getTransportParams();
		self::assertEquals( [
			'host'     => 'localhost',
			'port'     => 25,
			'username' => 'test',
			'password' => '',
			'timeout'  => 5
		], $tp);
	}

	public function testSetFrom() {
		$m = new SimpleMail();
		$m->setFrom('sergey.shuchkin@gmail.com', 'Sergey');
		self::assertEquals( 'sergey.shuchkin@gmail.com', $m->getFromEmail());
		self::assertEquals( 'Sergey', $m->getFromName());
	}

	public function testSend() {
		$m = new SimpleMail();
		$m->setFrom('example@example.com')
			->setTo('sergey.shuchkin@gmail.com')
			->setSubject('test_subject')
			->setText('test_text');
		self::assertTrue( $m->send() );
	}

	public function testSetTextAndAddText() {
		$m = new SimpleMail();
		$m->setText('line 1')->addText('line 2');
		self::assertEquals( "line 1\r\nline 2", $m->getText());
	}

	public function testSetPriority() {
		$m = new SimpleMail();
		$m->setPriority('urgent');
		self::assertEquals( 'urgent', $m->getPriority());
		$this->expectException( InvalidArgumentException::class );
		$m->setPriority('EXCEPT');
	}

	public function testToArray() {
		$m    = new SimpleMail();
		$html = '<html><body><p>test</p></body></html>';
		$m->setTo( 'sergey.shuchkin@gmail.com', 'Sergey' )
		  ->setReplyTo( 'segey@shuchkin.ru', 'S' )
		  ->setFrom( 'example@example.com' )
		  ->setHTML( $html, true )
		  ->setPriority( 'urgent' );

		$test = [
			'toName'        => 'Sergey',
			'toEmail'       => 'sergey.shuchkin@gmail.com',
			'fromName'      => '',
			'fromEmail'     => 'example@example.com',
			'replyName'     => 'S',
			'replyEmail'    => 'segey@shuchkin.ru',
			'subject'       => '',
			'text'          => 'test',
			'html'          => '<html><body><p>test</p></body></html>',
			'attachments'   => [],
			'priority'      => 'urgent',
			'customHeaders' => [],
		];

		self::assertEquals( $m->toArray(), $test );
	}

	public function testSetHTML() {
		$m    = new SimpleMail();
		$m->setHTML( '<html><body><p>test</p></body></html>', true );
		self::assertEquals( '<html><body><p>test</p></body></html>', $m->getHTML());
		self::assertEquals( 'test', $m->getText());
	}

	public function testSetSubject() {
		$m = new SimpleMail();
		$m->setSubject('TEST');
		self::assertEquals( 'TEST', $m->getSubject());
	}

	public function testAttach() {
		$m = new SimpleMail();
		$m->attach( 'image/test.jpg' );
		self::assertEquals( ['test.jpg' => 'image/test.jpg'], $m->getAttachments());
	}

	public function testCompose() {
		$m = new SimpleMail();
		$m->setFrom('sergey.shuchkin@gmail.com');
		$m2 = $m->compose('example@example.com', 'test_subj', 'text_message' );
		self::assertEquals( 'sergey.shuchkin@gmail.com', $m2->getFromEmail());
		self::assertEquals( 'example@example.com', $m2->getToEmail());
		self::assertEquals( 'test_subj', $m2->getSubject());
		self::assertEquals( 'text_message', $m2->getText());

	}

	public function testSetTransport() {
		$m = new SimpleMail();
		$m->setTransport('smtp', ['host' => 'example.com']);
		self::assertEquals( [
			'host' => 'example.com',
			'port' => 25,
			'username' => '',
			'password' => '',
			'timeout' => 5,
		], $m->getTransportParams());
	}
	public function testSetCustomTransport() {
		$mail = new Shuchkin\SimpleMail( function( SimpleMail $mail, $encoded ) {
			self::assertEquals( 'WARNING!', $mail->getSubject());
			self::assertEquals( '=?UTF-8?B?V0FSTklORyE=?=', $encoded['subject']);
		});
		$mail->setFrom('example@example.com')
		     ->setTo('sergey.shuchkin@gmail.com')
		     ->setSubject('WARNING!')
		     ->setText('SERVER DOWN!')
		     ->send();
	}

	public function testFromArray() {
		$m = new SimpleMail();

		$a = [
			'fromEmail' => 'example@example.com',
			'toEmail' => 'sergey.shuchkin@gmail.com',
			'unknownField' => 1
		];
		$m2 = $m->fromArray( $a );
		self::assertEquals( 'example@example.com', $m2->getFromEmail());
		self::assertEquals( 'sergey.shuchkin@gmail.com', $m2->getToEmail());

	}
	public function testFromJSON() {
		$m = new SimpleMail();
		$m2 = $m->fromJSON('{"toName":"Sergey","toEmail":"sergey.shuchkin@gmail.com","fromName":"Ex","fromEmail":"example@example.com","replyName":"","replyEmail":"","subject":"","text":"test text","html":"","attachments":[],"priority":"","customHeaders":[]}');
		self::assertEquals( 'example@example.com', $m2->getFromEmail());
		self::assertEquals( 'Ex', $m2->getFromName());
		self::assertEquals( 'sergey.shuchkin@gmail.com', $m2->getToEmail());
	}

	public function testTo() {
		$m = new SimpleMail();
		$m->setFrom('sergey.shuchkin@gmail.com');
		$m2 = $m->to('example@example.com');
		self::assertEquals( 'sergey.shuchkin@gmail.com', $m2->getFromEmail());
		self::assertEquals( 'example@example.com', $m2->getToEmail());
	}

}
