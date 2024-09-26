<?php

declare(strict_types=1);

namespace Php\Dns\Tests;

use Php\Dns\Credentials;
use Php\Dns\Dsn;
use Php\Dns\Exceptions\SyntaxException;
use Php\Dns\PathDsn;
use Php\Dns\StringParser;
use Php\Dns\UrlDsn;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StringParserTest extends TestCase
{
    public static function validDsnProvider(): iterable
    {
        yield ['localhost', new UrlDsn(null, 'localhost')]; //ok
        yield ['redis', new UrlDsn(null, 'redis')]; //ok
        yield ['localhost:8080', new UrlDsn(null, 'localhost', 8080)]; //ok
        yield ['http://localhost:8080', new UrlDsn('http', 'localhost', 8080)]; //ok
        yield ['http://localhost', new UrlDsn('http', 'localhost')]; //ok
        yield ['127.0.0.1:80', new UrlDsn(null, '127.0.0.1', 80)]; // ok

        yield ['https://user:pass@example.com', new UrlDsn('https', 'example.com', null, null, [], new Credentials('user', 'pass'))]; // ok
        yield ['sqs://user:B3%26iX%5EiOCLN%2Ab@aws.com', new UrlDsn('sqs', 'aws.com', null, null, [], new Credentials('user', 'B3&iX^iOCLN*b'))]; // ok

        yield ['memcached://127.0.0.1/50', new UrlDsn('memcached', '127.0.0.1', null, '/50')]; // ok
        yield ['memcached://localhost:11222?weight=25', new UrlDsn('memcached', 'localhost', 11222, null, ['weight' => '25'])]; // ok
        yield ['memcached://user:password@127.0.0.1?weight=50', new UrlDsn('memcached', '127.0.0.1', null, null, ['weight' => '50'], new Credentials('user', 'password'))]; // ok
        yield ['memcached://:password@127.0.0.1?weight=50', new UrlDsn('memcached', '127.0.0.1', null, null, ['weight' => '50'], new Credentials(null, 'password'))]; // ok
        yield ['memcached://user@127.0.0.1?weight=50', new UrlDsn('memcached', '127.0.0.1', null, null, ['weight' => '50'], new Credentials('user', null))]; // ok
        yield ['memcached://localhost?host[foo.bar]=3', new UrlDsn('memcached', 'localhost', null, null, ['host' => ['foo.bar' => '3']])]; // ok

        yield ['memcached:///var/run/memcached.sock?weight=25', new PathDsn('memcached', '/var/run/memcached.sock', ['weight' => '25'])]; // ok
        yield ['memcached://user:password@/var/local/run/memcached.socket?weight=25', new PathDsn('memcached', '/var/local/run/memcached.socket', ['weight' => '25'], new Credentials('user', 'password'))];

        yield ['redis:?host[redis1]&host[redis2]&host[redis3]&redis_cluster=1&redis_sentinel=mymaster', new Dsn('redis', ['host' => ['redis1' => '', 'redis2' => '', 'redis3' => ''], 'redis_cluster' => '1', 'redis_sentinel' => 'mymaster'])];
        yield ['redis:?host[h1]&host[h2]&host[/foo:]', new Dsn('redis', ['host' => ['h1' => '', 'h2' => '', '/foo:' => '']])];
        yield ['rediss:?host[h1]&host[h2]&host[/foo:]', new Dsn('rediss', ['host' => ['h1' => '', 'h2' => '', '/foo:' => '']])];

        yield ['dummy://a', new UrlDsn('dummy', 'a')];
        yield ['null://', new Dsn('null')];  //ok
        yield ['sync://', new Dsn('sync')]; //ok
        yield ['in-memory://', new Dsn('in-memory')]; // ok
        yield ['amqp://host/%2f/custom', new UrlDsn('amqp', 'host', null, '/%2f/custom')]; // ok
        yield ['mysql+unix://user:pass@/tmp/mysql.sock/dbname', new PathDsn('mysql+unix', '/tmp/mysql.sock/dbname', [], new Credentials('user', 'pass'))];

        yield ['file:///usr/var/dbname', new PathDsn('file', '/usr/var/dbname')]; // ok
        yield ['scheme:///var/local/run/memcached.socket?weight=25', new PathDsn('scheme', '/var/local/run/memcached.socket', ['weight' => '25'])]; // ok

        yield ['amqp://localhost:5672/%2f/messages?' .
            'queues[messages][arguments][x-dead-letter-exchange]=dead-exchange&' .
            'queues[messages][arguments][x-message-ttl]=100&' .
            'queues[messages][arguments][x-delay]=100&' .
            'queues[messages][arguments][x-expires]=150&',
            new UrlDsn('amqp', 'localhost', 5672, '/%2f/messages', [
                'queues' => [
                    'messages' => [
                        'arguments' => [
                            'x-dead-letter-exchange' => 'dead-exchange',
                            'x-message-ttl' => '100',
                            'x-delay' => '100',
                            'x-expires' => '150',
                        ],
                    ],
                ],
            ]),
        ]; // ok
        yield ['redis:///var/run/redis/redis.sock', new PathDsn('redis', '/var/run/redis/redis.sock')]; // ok
    }

    #[Test]
    #[DataProvider('validDsnProvider')]
    public function parse(string $dsn, $expected): void
    {
        $result = (new StringParser())->parse($dsn);
        $this->assertEquals($expected, $result);
    }

    public static function fromWrongStringProvider(): iterable
    {
        yield 'garbage at the end' => ['dummy://a some garbage here'];
        yield 'not a valid DSN' => ['something not a dsn'];
        yield 'failover not closed' => ['failover(dummy://a'];
        yield ['(dummy://a)'];
        yield ['foo(dummy://a bar()'];
        yield [''];
        yield ['foo(dummy://a bar())'];
        yield ['foo()'];
        yield ['amqp://user:pass:word@localhost'];
        yield ['amqp://user:pass@word@localhost'];
        yield ['amqp://user:pass/word@localhost'];
        yield ['amqp://user:pass/word@localhost'];
        yield ['amqp://user@name:pass@localhost'];
        yield ['amqp://user/name:pass@localhost'];
    }

    #[Test]
    #[DataProvider('fromWrongStringProvider')]
    public function parseInvalid(string $dsn): void
    {
        $this->expectException(SyntaxException::class);

        (new StringParser())->parse($dsn);
    }

    public static function utf8HostsProvider(): iterable
    {
        yield ['http://ουτοπία.δπθ.gr/', 'ουτοπία.δπθ.gr'];
        yield ['https://程式设计.com/', '程式设计.com'];
        yield ['https://привет.рф/', 'привет.рф'];
    }

    #[Test]
    #[DataProvider('utf8HostsProvider')]
    public function testUtf8Host(string $dsn, $expected)
    {
        /** @var UrlDsn $urlDsn */
        $urlDsn = (new StringParser())->parse($dsn);
        $this->assertSame($expected, $urlDsn->getHost());
    }

    #[Test]
    public function testParseUrl()
    {
        /** @var UrlDsn $dsn */
        $dsn = (new StringParser())->parse(
            'amqp://user:pass@localhost:5672/%2f/messages?queues[messages][arguments][x-delay]=100&'
        );
        $this->assertInstanceOf(UrlDsn::class, $dsn);
        $this->assertSame('localhost', $dsn->getHost());
        $this->assertSame(5672, $dsn->getPort());
        $this->assertSame('amqp', $dsn->getScheme());
        $this->assertSame('/%2f/messages', $dsn->getPath());
        $this->assertSame('user', $dsn->getAuthentication()->user);
        $this->assertSame('pass', $dsn->getAuthentication()->password);
        $this->assertSame([
            'queues' => [
                'messages' => [
                    'arguments' => [
                        'x-delay' => '100'
                    ]
                ]
            ]
        ], $dsn->getParameters());

        $this->assertSame([
            'messages' => [
                'arguments' => [
                    'x-delay' => '100'
                ]
            ]
        ], $dsn->getParameter('queues'));

        $this->assertSame(
            'amqp://user:pass@localhost:5672///messages?queues[messages][arguments][x-delay]=100',
            rawurldecode((string)$dsn)
        );
    }

    #[Test]
    public function testParsePath()
    {
        /** @var PathDsn $dsn */
        $dsn = (new StringParser())->parse('redis:///var/run/redis/redis.sock?weight=25');
        $this->assertInstanceOf(PathDsn::class, $dsn);

        $this->assertSame('redis', $dsn->getScheme());
        $this->assertSame('/var/run/redis/redis.sock', $dsn->getPath());
        $this->assertSame(['weight' => '25'], $dsn->getParameters());
        $this->assertSame('redis:///var/run/redis/redis.sock?weight=25', (string)$dsn);
    }

    #[Test]
    public function testParseDns()
    {
        $dsn = (new StringParser())->parse('rediss:?host[h1]&host[h2]&host[/foo:]');
        $this->assertSame(Dsn::class, $dsn::class);

        $this->assertSame('rediss', $dsn->getScheme());
        $this->assertSame(['host' => ['h1' => '', 'h2' => '', '/foo:' => '']], $dsn->getParameters());
        $this->assertSame(['h1' => '', 'h2' => '', '/foo:' => ''], $dsn->getParameter('host'));
        $this->assertSame('rediss://?host[h1]=&host[h2]=&host[/foo:]=', rawurldecode((string)$dsn));

        $dsn = (new StringParser())->parse('rediss:?');
        $this->assertSame('rediss', $dsn->getScheme());
        $this->assertSame([], $dsn->getParameters());
        $this->assertNull($dsn->getParameter('test'));

        $this->assertSame('rediss://', rawurldecode((string)$dsn));
    }
}
