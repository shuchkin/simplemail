<?php
/**
 * Created by PhpStorm.
 * User: Serg
 * Date: 14.02.2019
 * Time: 21:17
 */

use Shuchkin\SimpleMail;
use PHPUnit\Framework\TestCase;

class SimpleMailTest extends TestCase {

	public function testSetReply() {
		$m = new SimpleMail();
		$m->setReplyTo('sergey.shuchkin@gmail.com', 'Sergey');
		self::assertEquals( $m->getReplyToName(), 'Sergey' );
	}

	public function testGetTransportParams() {
		$m = new SimpleMail('smtp', [ 'username' => 'test'] );
		$tp = $m->getTransportParams();
		self::assertEquals( $tp, [
			'host'     => 'localhost',
			'port'     => 25,
			'username' => 'test',
			'password' => '',
			'timeout'  => 5,
			'headers' => []
		] );
	}

	public function testSetFrom() {
		$m = new SimpleMail();
		$m->setFrom('sergey.shuchkin@gmail.com', 'Sergey');
		self::assertEquals( $m->getFromEmail(), 'sergey.shuchkin@gmail.com' );
		self::assertEquals( $m->getFromName(), 'Sergey' );
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
		self::assertEquals( $m->getText(), "line 1\r\nline 2" );
	}

	public function testSetPriority() {
		$m = new SimpleMail();
		$m->setPriority('urgent');
		self::assertEquals( $m->getPriority(), 'urgent' );
		$this->expectException( \InvalidArgumentException::class );
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
		self::assertEquals( $m->getHTML(),'<html><body><p>test</p></body></html>');
		self::assertEquals( $m->getText(), 'test' );
	}

	public function testSetSubject() {
		$m = new SimpleMail();
		$m->setSubject('TEST');
		self::assertEquals( $m->getSubject(), 'TEST' );
	}

	public function testAttach() {
		$m = new SimpleMail();
		$m->attach( 'image/test.jpg' );
		self::assertEquals( $m->getAttachments(), ['test.jpg' => 'image/test.jpg'] );
	}

	public function testCompose() {
		$m = new SimpleMail();
		$m->setFrom('sergey.shuchkin@gmail.com');
		$m2 = $m->compose('example@example.com', 'test_subj', 'text_message' );
		self::assertEquals( $m2->getFromEmail(), 'sergey.shuchkin@gmail.com' );
		self::assertEquals( $m2->getToEmail(), 'example@example.com' );
		self::assertEquals( $m2->getSubject(), 'test_subj' );
		self::assertEquals( $m2->getText(), 'text_message' );

	}

	public function testSetTransport() {
		$m = new SimpleMail();
		$m->setTransport('smtp', ['host' => 'example.com']);
		self::assertEquals( $m->getTransportParams(), [
			'host' => 'example.com',
			'port' => 25,
			'username' => '',
			'password' => '',
			'timeout' => 5,
		] );
	}
	public function testSetCustomTransport() {
		$mail = new Shuchkin\SimpleMail( function( $mail, $encoded ) {
			print_r( $mail );
			print_r( $encoded );
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
		self::assertEquals( $m2->getFromEmail(), 'example@example.com' );
		self::assertEquals( $m2->getToEmail(), 'sergey.shuchkin@gmail.com' );
		self::assertObjectNotHasAttribute( 'unknowField', $m2 );

	}
	public function testFromJSON() {
		$m = new SimpleMail();
		$m2 = $m->fromJSON('{"toName":"Sergey","toEmail":"sergey.shuchkin@gmail.com","fromName":"Ex","fromEmail":"example@example.com","replyName":"","replyEmail":"","subject":"","text":"test text","html":"","attachments":[],"priority":"","customHeaders":[]}');
		self::assertEquals( $m2->getFromEmail(), 'example@example.com' );
		self::assertEquals( $m2->getFromName(), 'Ex' );
		self::assertEquals( $m2->getToEmail(), 'sergey.shuchkin@gmail.com' );
	}

	public function testTo() {
		$m = new SimpleMail();
		$m->setFrom('sergey.shuchkin@gmail.com');
		$m2 = $m->to('example@example.com');
		self::assertEquals( $m2->getFromEmail(), 'sergey.shuchkin@gmail.com' );
		self::assertEquals( $m2->getToEmail(), 'example@example.com' );
	}

	public function testToJSON() {
		$m = new SimpleMail();
		$m->setFrom('example@example.com', 'Ex')
			->setTo('sergey.shuchkin@gmail.com', 'Sergey')
			->setText('test text');

		self::assertJsonStringEqualsJsonString( $m->toJSON(), '{"toName":"Sergey","toEmail":"sergey.shuchkin@gmail.com","fromName":"Ex","fromEmail":"example@example.com","replyName":"","replyEmail":"","subject":"","text":"test text","html":"","attachments":[],"priority":"","customHeaders":[]}');
	}
}
