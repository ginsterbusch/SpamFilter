<?php
namespace spamfilter;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-08-20 at 23:15:47.
 */
class SpamFilterTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @var \spamfilter\KnowledgeInterface|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $knowledge;

	/**
	 * @var SpamFilter
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->knowledge = $this->getMock('\spamfilter\KnowledgeInterface');

		$this->knowledge
			->expects($this->any())
			->method('getWord')
			->will($this->throwException(new \OutOfRangeException()));

		$this->object = new SpamFilter($this->knowledge);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
	}

	/**
	 * @covers spamfilter\SpamFilter::learn
	 */
	public function testLearn() {
		$this->knowledge
			->expects($this->any())
			->method('getWords')
			->with($this->equalTo(array('this', 'sample', 'text')))
			->will($this->returnValue(array(
			array('word' => 'this', 'spam' => 0, 'ham' => 1),
			array('word' => 'sample', 'spam' => 0, 'ham' => 5),
			array('word' => 'text', 'spam' => 3, 'ham' => 1)
		)));

		$oArr = $this->object->learn('This is a sample text', false);

		$this->assertEquals('this', $oArr[0]['word']);
		$this->assertEquals(0, $oArr[0]['spam']);
		$this->assertEquals(1, $oArr[0]['ham']);

		$this->assertEquals('sample', $oArr[1]['word']);
		$this->assertEquals(0, $oArr[1]['spam']);
		$this->assertEquals(1, $oArr[1]['ham']);

		$this->assertEquals('text', $oArr[2]['word']);
		$this->assertEquals(0, $oArr[2]['spam']);
		$this->assertEquals(1, $oArr[2]['ham']);
	}

	/**
	 * @covers spamfilter\SpamFilter::rate
	 */
	public function testRate() {
		$this->knowledge
			->expects($this->any())
			->method('getWords')
			->with($this->equalTo(array('this', 'http', 'https', 'text', 'yada')))
			->will($this->returnValue(array(
			array('word' => 'this', 'spam' => 0, 'ham' => 1),
			array('word' => 'http', 'spam' => 0, 'ham' => 0),
			array('word' => 'https', 'spam' => 0, 'ham' => 0),
			array('word' => 'text', 'spam' => 0, 'ham' => 5),
			array('word' => 'yada', 'spam' => 3, 'ham' => 1)
		)));


		$iArr = array(
			'text' => 'This <a href="http://foo.com">is</a> a https://bar.net text ftp://www.yada.yada.pl text www.foo.bar.dot.com',
			'user' => 'user@test.com'
		);

		$result = $this->object->rate($iArr, null, null, null, true);

		$this->assertArrayHasKey('links', $result);
		$this->assertArrayHasKey('body', $result);
		$this->assertArrayHasKey('prop', $result);
		$this->assertArrayHasKey('total', $result);
		$this->assertArrayHasKey('rate', $result);

		$this->assertEquals(-2, $result['links']);
		$this->assertEquals(2, $result['body']);
		$this->assertEquals(-3.1578947368421, $result['prop']);
		$this->assertEquals(-3.1578947368421, $result['total']);
		$this->assertEquals(0, $result['rate']);

		$result = $this->object->rate($iArr, null, false);

		$this->assertEquals(-1, $result);
	}

	/**
	 * @covers spamfilter\SpamFilter::rate
	 */
	public function testRateRequest() {
		$this->knowledge
			->expects($this->any())
			->method('getWords')
			->with($this->equalTo(array('this', 'http', 'https', 'text', 'yada')))
			->will($this->returnValue(array(
			array('word' => 'this', 'spam' => 0, 'ham' => 1),
			array('word' => 'http', 'spam' => 0, 'ham' => 0),
			array('word' => 'https', 'spam' => 0, 'ham' => 0),
			array('word' => 'text', 'spam' => 0, 'ham' => 5),
			array('word' => 'yada', 'spam' => 3, 'ham' => 1)
		)));


		$iArr = array(
			'text' => 'This <a href="http://foo.com">is</a> a https://bar.net text ftp://www.yada.yada.pl text www.foo.bar.dot.com',
			'user' => 'user@test.com'
		);

		$result = $this->object->rate($iArr, 'foo', '', null, true);

		$this->assertArrayHasKey('links', $result);
		$this->assertArrayHasKey('body', $result);
		$this->assertArrayHasKey('prop', $result);
		$this->assertArrayHasKey('total', $result);
		$this->assertArrayHasKey('rate', $result);

		$this->assertEquals(-2, $result['links']);
		$this->assertEquals(2, $result['body']);
		$this->assertEquals(-3.1578947368421, $result['prop']);
		$this->assertEquals(0, $result['history']);
		$this->assertEquals(0, $result['exists']);
		$this->assertEquals(-100, $result['honeypot']);
		$this->assertEquals(-100, $result['referer']);
		$this->assertEquals(-203.1578947368421, $result['total']);
		$this->assertEquals(-1, $result['rate']);

		$result = $this->object->rate($iArr, 'foo', '', null, false);

		$this->assertEquals(-1, $result);
	}

	/**
	 * @covers spamfilter\SpamFilter::links
	 */
	public function testLinks() {
		$result = $this->object->links('This is a sample text');
		$this->assertEquals(2, $result);

		$result = $this->object->links('This <a href="http://foo.com">is</a> a sample text');
		$this->assertEquals(1, $result);

		$result = $this->object->links('This <a href="http://foo.com">is</a> a https://bar.net sample text');
		$this->assertEquals(0, $result);

		$result = $this->object->links('This <a href="http://foo.com">is</a> a https://bar.net sample ftp://www.yada.yada.pl text');
		$this->assertEquals(-1, $result);

		$result = $this->object->links('This <a href="http://foo.com">is</a> a https://bar.net text ftp://www.yada.yada.pl text www.foo.bar.dot.com');
		$this->assertEquals(-2, $result);
	}

	/**
	 * @covers spamfilter\SpamFilter::body
	 */
	public function testBody() {
		$result = $this->object->body('This is sample text');
		$this->assertEquals(-2, $result);

		$result = $this->object->body('This is sample text This is sample text This is sample text This is sample text This is sample text This is sample text This is sample text This is sample text This is sample text This is sample text This is sample text This is sample text This is sample text This is sample text');
		$this->assertEquals(2, $result);
	}

	/**
	 * @covers spamfilter\SpamFilter::prop
	 */
	public function testProp() {
		$this->knowledge
			->expects($this->any())
			->method('getWords')
			->with($this->equalTo(array('this', 'sample', 'text')))
			->will($this->returnValue(array(array('word' => 'this', 'spam' => 0, 'ham' => 1), array('word' => 'sample', 'spam' => 0, 'ham' => 5), array('word' => 'text', 'spam' => 3, 'ham' => 1))));

		$result = $this->object->prop('This is sample text');
		$this->assertEquals(-3.0, $result);
	}

	/**
	 * @covers spamfilter\SpamFilter::history
	 */
	public function testHistory() {
		$this->assertEquals(10, $this->object->history(10));
	}

	/**
	 * @covers spamfilter\SpamFilter::existing
	 */
	public function testExisting() {
		$this->assertEquals(-100, $this->object->existing(1));
	}

	/**
	 * @covers spamfilter\SpamFilter::honeypot
	 */
	public function testHoneypot() {
		$this->assertEquals(-100, $this->object->honeypot(false));
		$this->assertEquals(0, $this->object->honeypot(true));
	}

	/**
	 * @covers spamfilter\SpamFilter::referer
	 */
	public function testReferer() {
		$this->assertEquals(-100, $this->object->referer(false));
		$this->assertEquals(0, $this->object->referer(true));
	}

	/**
	 * @covers spamfilter\SpamFilter::elapsed
	 */
	public function testElapsed() {
		$this->assertEquals(-100, $this->object->elapsed(1.123));
		$this->assertEquals(0, $this->object->elapsed(10));
	}
}
