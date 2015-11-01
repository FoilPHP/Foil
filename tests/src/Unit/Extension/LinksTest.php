<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Unit\Extension;

use Foil\Tests\TestCase;
use Foil\Extensions\Links;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class LinksTest extends TestCase
{

    public function testProvideFunctions()
    {
        $links = new Links();
        $functions = $links->provideFunctions();
        foreach ($functions as $function) {
            assertInternalType('callable', $function);
        }
    }

    public function testLinkNoDomainNoDirs()
    {
        $links = new Links();
        assertSame('/foo/bar', $links->link('foo/bar/', 'foo', 'https'));
    }

    public function testLinkDomainNoDirs()
    {
        $links1 = new Links();
        $links1->setup([
            'host'        => 'www.example.com',
            'assets_host' => 'static.example.com',
            'assets_path' => 'path/to/',
        ]);
        $links2 = new Links();
        $links2->setup(['host' => 'http://example.com']);
        assertSame('https://www.example.com/foo/bar', $links1->link('foo/bar/', 'foo', 'https'));
        assertSame('http://example.com/foo/bar', $links2->link('foo/bar/', 'foo', 'bar'));
    }

    public function testLinkNoDomainDirs()
    {
        $links = new Links();
        $links->setup([
            'urls' => ['pdf' => '/url/to/pdf/', 'doc' => 'url/to/doc'],
        ]);
        assertSame('/url/to/pdf/foo.pdf', $links->link('foo.pdf', 'pdf'));
        assertSame('/url/to/doc/foo.doc', $links->link('foo.doc', 'doc'));
    }

    public function testLinkDomainDirs()
    {
        $links = new Links();
        $links->setup([
            'urls'        => ['pdf' => '/url/to/pdf/'],
            'host'        => 'example.com',
            'assets_host' => 'static.example.com',
        ]);
        assertSame('http://example.com/url/to/pdf/foo.pdf', $links->link('foo.pdf', 'pdf'));
        assertSame('https://example.com/url/to/pdf/foo.pdf',
            $links->link('foo.pdf', 'pdf', 'https'));
    }

    public function testAssetNothing()
    {
        $links = new Links();
        assertSame('/foo.js', $links->asset('foo.js'));
    }

    public function testAssetOnlyUrl()
    {
        $links = new Links();
        $links->setup(['assets_url' => '/assets/']);
        assertSame('/assets/foo.js', $links->asset('foo.js'));
    }

    public function testAssetOnlyDomain()
    {
        $links = new Links();
        $links->setup(['host' => 'example.com']);
        $links2 = new Links();
        $links2->setup(['host' => 'http://example.com']);
        $links3 = new Links();
        $links3->setup(['assets_host' => 'static.example.com']);
        $links4 = new Links();
        $links4->setup(['host' => 'auto']);
        assertSame('http://example.com/foo.js', $links->asset('foo.js'));
        assertSame('https://example.com/foo.js', $links->asset('foo.js', 'https'));
        assertSame('//example.com/foo.js', $links->asset('foo.js', false));
        assertSame('http://example.com/foo.js', $links2->asset('foo.js'));
        assertSame('https://example.com/foo.js', $links2->asset('foo.js', 'https'));
        assertSame('//example.com/foo.js', $links2->asset('foo.js', false));
        assertSame('http://static.example.com/foo.js', $links3->asset('foo.js'));
        assertSame('https://static.example.com/foo.js', $links3->asset('foo.js', 'https'));
        assertSame('//static.example.com/foo.js', $links3->asset('foo.js', false));
        // in tests filter_input(INPUT_SERVER, 'SERVER_NAME') return NULL
        assertSame('/foo.js', $links4->asset('foo.js'));
    }

    public function testAssetOnlyCache()
    {
        $path = getenv('FOIL_TESTS_BASEPATH').'/_files/assets';
        $links1 = new Links();
        $links1->setup(['assets_path' => $path, 'cache_bust' => true]);
        $links2 = new Links();
        $links2->setup(['assets_path' => $path, 'cache_bust' => 'images']);
        $links3 = new Links();
        $links3->setup(['assets_path' => $path, 'cache_bust' => 'scripts']);
        $links4 = new Links();
        $links4->setup(['assets_path' => $path, 'cache_bust' => 'styles']);
        $links5 = new Links();
        $links5->setup(['assets_path' => $path, 'cache_bust' => ['js', '.jpg']]);
        // timestamp in 10 chars is from 09 Sept 2001 to 20 Nov 2286
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.js$|', $links1->asset('foo.js')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.jpg$|', $links1->asset('foo.jpg')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.css$|', $links1->asset('foo.css')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.js$|', $links2->asset('foo.js')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.jpg$|', $links2->asset('foo.jpg')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.css$|', $links2->asset('foo.css')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.js$|', $links3->asset('foo.js')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.jpg$|', $links3->asset('foo.jpg')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.css$|', $links3->asset('foo.css')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.js$|', $links4->asset('foo.js')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.jpg$|', $links4->asset('foo.jpg')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.css$|', $links4->asset('foo.css')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.js$|', $links5->asset('foo.js')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.jpg$|', $links5->asset('foo.jpg')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.css$|', $links5->asset('foo.css')));
    }

    public function testAssetDomainAndCache()
    {
        $links1 = new Links();
        $links1->setup([
            'assets_path' => getenv('FOIL_TESTS_BASEPATH').'/_files/assets',
            'cache_bust'  => ['js', '.jpg'],
            'host'        => 'foo.example.com',
        ]);
        $links2 = new Links();
        $links2->setup([
            'assets_path' => getenv('FOIL_TESTS_BASEPATH').'/_files/assets',
            'cache_bust'  => ['js', '.jpg'],
            'host'        => 'foo.example.com',
            'assets_host' => 'static.example.com',
        ]);
        $regex1 = '^http://foo.example.com/foo\.[0-9]{10}\.';
        $regex2 = '^http://static.example.com/foo\.[0-9]{10}\.';
        assertSame(1, preg_match("|{$regex1}js$|", $links1->asset('foo.js')));
        assertSame(1, preg_match("|{$regex1}jpg$|", $links1->asset('foo.jpg')));
        assertSame('http://foo.example.com/foo.css', $links1->asset('foo.css'));
        assertSame(1, preg_match("|{$regex2}js$|", $links2->asset('foo.js')));
        assertSame(1, preg_match("|{$regex2}jpg$|", $links2->asset('foo.jpg')));
        assertSame('http://static.example.com/foo.css', $links2->asset('foo.css'));
    }

    public function testAssetDomainAndUrl()
    {
        $links1 = new Links();
        $links1->setup([
            'assets_url' => '/assets/',
            'host'       => 'foo.example.com',
        ]);
        $links2 = new Links();
        $links2->setup([
            'assets_url'  => '/assets/',
            'host'        => 'foo.example.com',
            'assets_host' => 'static.example.com',
        ]);
        assertSame('http://foo.example.com/assets/foo.js', $links1->asset('foo.js'));
        assertSame('http://static.example.com/assets/foo.js', $links2->asset('foo.js'));
    }

    public function testAssetCacheAndUrl()
    {
        $links = new Links();
        $links->setup([
            'assets_path' => getenv('FOIL_TESTS_BASEPATH').'/_files/assets',
            'cache_bust'  => ['js', '.jpg'],
            'assets_url'  => '/assets/',
        ]);
        $regex = '^/assets/foo\.[0-9]{10}\.';
        assertSame(1, preg_match("|{$regex}js$|", $links->asset('foo.js')));
        assertSame(1, preg_match("|{$regex}jpg$|", $links->asset('foo.jpg')));
        assertSame('/assets/foo.css', $links->asset('foo.css'));
    }

    public function testAssetDomainAndCacheAndUrl()
    {
        $links1 = new Links();
        $links1->setup([
            'host'        => 'foo.example.com',
            'assets_path' => getenv('FOIL_TESTS_BASEPATH').'/_files/assets',
            'cache_bust'  => ['js', '.jpg'],
            'assets_url'  => '/assets/',
        ]);
        $regex1 = '^http://foo.example.com/assets/foo\.[0-9]{10}\.';
        $links2 = new Links();
        $links2->setup([
            'host'        => 'foo.example.com',
            'assets_host' => 'static.example.com',
            'assets_path' => getenv('FOIL_TESTS_BASEPATH').'/_files/assets',
            'cache_bust'  => ['js', '.jpg'],
            'assets_url'  => '/assets/',
        ]);
        $regex2 = '^http://static.example.com/assets/foo\.[0-9]{10}\.';
        assertSame(1, preg_match("|{$regex1}js$|", $links1->asset('foo.js')));
        assertSame(1, preg_match("|{$regex1}jpg$|", $links1->asset('foo.jpg')));
        assertSame('http://foo.example.com/assets/foo.css', $links1->asset('foo.css'));
        assertSame(1, preg_match("|{$regex2}js$|", $links2->asset('foo.js')));
        assertSame(1, preg_match("|{$regex2}jpg$|", $links2->asset('foo.jpg')));
        assertSame('http://static.example.com/assets/foo.css', $links2->asset('foo.css'));
    }
}
