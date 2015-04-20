<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Tests\Extension;

use Foil\Tests\TestCase;
use Foil\Extensions\Links;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class LinksTest extends TestCase
{
    public function testLinkNoDomainNoDirs()
    {
        $l = new Links();
        assertSame('/foo/bar', $l->link('foo/bar/', 'foo', 'https'));
    }

    public function testLinkDomainNoDirs()
    {
        $l1 = new Links();
        $l1->setup([
            'host'        => 'www.example.com',
            'assets_host' => 'static.example.com',
            'assets_path' => 'path/to/',
        ]);
        $l2 = new Links();
        $l2->setup(['host' => 'http://example.com']);
        assertSame('https://www.example.com/foo/bar', $l1->link('foo/bar/', 'foo', 'https'));
        assertSame('http://example.com/foo/bar', $l2->link('foo/bar/', 'foo', 'bar'));
    }

    public function testLinkNoDomainDirs()
    {
        $l = new Links();
        $l->setup([
            'urls' => ['pdf' => '/url/to/pdf/', 'doc' => 'url/to/doc'],
        ]);
        assertSame('/url/to/pdf/foo.pdf', $l->link('foo.pdf', 'pdf'));
        assertSame('/url/to/doc/foo.doc', $l->link('foo.doc', 'doc'));
    }

    public function testLinkDomainDirs()
    {
        $l = new Links();
        $l->setup([
            'urls'        => ['pdf' => '/url/to/pdf/'],
            'host'        => 'example.com',
            'assets_host' => 'static.example.com',
        ]);
        assertSame('http://example.com/url/to/pdf/foo.pdf', $l->link('foo.pdf', 'pdf'));
        assertSame('https://example.com/url/to/pdf/foo.pdf', $l->link('foo.pdf', 'pdf', 'https'));
    }

    public function testAssetNothing()
    {
        $l = new Links();
        assertSame('/foo.js', $l->asset('foo.js'));
    }

    public function testAssetOnlyUrl()
    {
        $l = new Links();
        $path = FOILTESTSBASEPATH.'/_files/assets';
        $l->setup(['assets_url' => '/assets/']);
        assertSame('/assets/foo.js', $l->asset('foo.js'));
    }

    public function testAssetOnlyDomain()
    {
        $path = FOILTESTSBASEPATH.'/_files/assets';
        $l = new Links();
        $l->setup(['host' => 'example.com']);
        $l2 = new Links();
        $l2->setup(['host' => 'http://example.com']);
        $l3 = new Links();
        $l3->setup(['assets_host' => 'static.example.com']);
        assertSame('http://example.com/foo.js', $l->asset('foo.js'));
        assertSame('https://example.com/foo.js', $l->asset('foo.js', 'https'));
        assertSame('//example.com/foo.js', $l->asset('foo.js', false));
        assertSame('http://example.com/foo.js', $l2->asset('foo.js'));
        assertSame('https://example.com/foo.js', $l2->asset('foo.js', 'https'));
        assertSame('//example.com/foo.js', $l2->asset('foo.js', false));
        assertSame('http://static.example.com/foo.js', $l3->asset('foo.js'));
        assertSame('https://static.example.com/foo.js', $l3->asset('foo.js', 'https'));
        assertSame('//static.example.com/foo.js', $l3->asset('foo.js', false));
    }

    public function testAssetOnlyCache()
    {
        $path = FOILTESTSBASEPATH.'/_files/assets';
        $l1 = new Links();
        $l1->setup(['assets_path' => $path, 'cache_bust' => true]);
        $l2 = new Links();
        $l2->setup(['assets_path' => $path, 'cache_bust' => 'images']);
        $l3 = new Links();
        $l3->setup(['assets_path' => $path, 'cache_bust' => 'scripts']);
        $l4 = new Links();
        $l4->setup(['assets_path' => $path, 'cache_bust' => 'styles']);
        $l5 = new Links();
        $l5->setup(['assets_path' => $path, 'cache_bust' => ['js', '.jpg']]);
        // timestamp in 10 chars is from 09 Sept 2001 to 20 Nov 2286
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.js$|', $l1->asset('foo.js')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.jpg$|', $l1->asset('foo.jpg')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.css$|', $l1->asset('foo.css')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.js$|', $l2->asset('foo.js')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.jpg$|', $l2->asset('foo.jpg')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.css$|', $l2->asset('foo.css')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.js$|', $l3->asset('foo.js')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.jpg$|', $l3->asset('foo.jpg')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.css$|', $l3->asset('foo.css')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.js$|', $l4->asset('foo.js')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.jpg$|', $l4->asset('foo.jpg')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.css$|', $l4->asset('foo.css')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.js$|', $l5->asset('foo.js')));
        assertSame(1, preg_match('|^/foo\.[0-9]{10}\.jpg$|', $l5->asset('foo.jpg')));
        assertSame(0, preg_match('|^/foo\.[0-9]{10}\.css$|', $l5->asset('foo.css')));
    }

    public function testAssetDomainAndCache()
    {
        $l1 = new Links();
        $l1->setup([
            'assets_path' => FOILTESTSBASEPATH.'/_files/assets',
            'cache_bust'  => ['js', '.jpg'],
            'host'        => 'foo.example.com',
        ]);
        $l2 = new Links();
        $l2->setup([
            'assets_path' => FOILTESTSBASEPATH.'/_files/assets',
            'cache_bust'  => ['js', '.jpg'],
            'host'        => 'foo.example.com',
            'assets_host' => 'static.example.com',
        ]);
        $regex1 = '^http://foo.example.com/foo\.[0-9]{10}\.';
        $regex2 = '^http://static.example.com/foo\.[0-9]{10}\.';
        assertSame(1, preg_match("|{$regex1}js$|", $l1->asset('foo.js')));
        assertSame(1, preg_match("|{$regex1}jpg$|", $l1->asset('foo.jpg')));
        assertSame('http://foo.example.com/foo.css', $l1->asset('foo.css'));
        assertSame(1, preg_match("|{$regex2}js$|", $l2->asset('foo.js')));
        assertSame(1, preg_match("|{$regex2}jpg$|", $l2->asset('foo.jpg')));
        assertSame('http://static.example.com/foo.css', $l2->asset('foo.css'));
    }

    public function testAssetDomainAndUrl()
    {
        $l1 = new Links();
        $l1->setup([
            'assets_url' => '/assets/',
            'host'       => 'foo.example.com',
        ]);
        $l2 = new Links();
        $l2->setup([
            'assets_url'  => '/assets/',
            'host'        => 'foo.example.com',
            'assets_host' => 'static.example.com',
        ]);
        assertSame('http://foo.example.com/assets/foo.js', $l1->asset('foo.js'));
        assertSame('http://static.example.com/assets/foo.js', $l2->asset('foo.js'));
    }

    public function testAssetCacheAndUrl()
    {
        $l = new Links();
        $l->setup([
            'assets_path' => FOILTESTSBASEPATH.'/_files/assets',
            'cache_bust'  => ['js', '.jpg'],
            'assets_url'  => '/assets/',
        ]);
        $regex = '^/assets/foo\.[0-9]{10}\.';
        assertSame(1, preg_match("|{$regex}js$|", $l->asset('foo.js')));
        assertSame(1, preg_match("|{$regex}jpg$|", $l->asset('foo.jpg')));
        assertSame('/assets/foo.css', $l->asset('foo.css'));
    }

    public function testAssetDomainAndCacheAndUrl()
    {
        $l1 = new Links();
        $l1->setup([
            'host'        => 'foo.example.com',
            'assets_path' => FOILTESTSBASEPATH.'/_files/assets',
            'cache_bust'  => ['js', '.jpg'],
            'assets_url'  => '/assets/',
        ]);
        $regex1 = '^http://foo.example.com/assets/foo\.[0-9]{10}\.';
        $l2 = new Links();
        $l2->setup([
            'host'        => 'foo.example.com',
            'assets_host' => 'static.example.com',
            'assets_path' => FOILTESTSBASEPATH.'/_files/assets',
            'cache_bust'  => ['js', '.jpg'],
            'assets_url'  => '/assets/',
        ]);
        $regex2 = '^http://static.example.com/assets/foo\.[0-9]{10}\.';
        assertSame(1, preg_match("|{$regex1}js$|", $l1->asset('foo.js')));
        assertSame(1, preg_match("|{$regex1}jpg$|", $l1->asset('foo.jpg')));
        assertSame('http://foo.example.com/assets/foo.css', $l1->asset('foo.css'));
        assertSame(1, preg_match("|{$regex2}js$|", $l2->asset('foo.js')));
        assertSame(1, preg_match("|{$regex2}jpg$|", $l2->asset('foo.jpg')));
        assertSame('http://static.example.com/assets/foo.css', $l2->asset('foo.css'));
    }
}
