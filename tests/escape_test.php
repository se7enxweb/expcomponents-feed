<?php
/**
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version //autogentag//
 * @filesource
 * @package Feed
 * @subpackage Tests
 */

/**
 * Every value is handed to ezcFeed as plain text and comes out of the
 * generated feed as the same text: "&", "<" and ">" are escaped exactly once,
 * whichever element or module writes the value.
 *
 * @package Feed
 * @subpackage Tests
 */
class ezcFeedEscapeTest extends ezcFeedTestCase
{
    const TEXT = 'Tom & Jerry <b>"1 < 2"</b> &amp;';

    public static function suite()
    {
        return new PHPUnit\Framework\TestSuite( __CLASS__ );
    }

    /**
     * A feed with TEXT in every text field the feed types know.
     *
     * @param string $type
     * @return ezcFeed
     */
    protected function createFeed( $type, &$item = null )
    {
        $feed = new ezcFeed();
        $feed->title = self::TEXT;
        $feed->description = self::TEXT;
        $feed->copyright = self::TEXT;
        $feed->id = 'http://example.com/?a=1&b=2';
        $feed->updated = 1234567890;
        $link = $feed->add( 'link' );
        $link->href = 'http://example.com/?a=1&b=2';

        if ( $type !== 'rss1' )
        {
            $author = $feed->add( 'author' );
            $author->name = self::TEXT;
            $author->email = 'author@example.com';
            $category = $feed->add( 'category' );
            $category->term = self::TEXT;
            $generator = $feed->add( 'generator' );
            $generator->name = self::TEXT;
        }

        $item = $feed->add( 'item' );
        $item->title = self::TEXT;
        $item->description = self::TEXT;
        $item->id = 'http://example.com/?i=1&j=2';
        $item->updated = 1234567890;
        $item->published = 1234567890;
        $link = $item->add( 'link' );
        $link->href = 'http://example.com/?i=1&j=2';

        if ( $type !== 'rss1' )
        {
            $author = $item->add( 'author' );
            $author->name = self::TEXT;
            $author->email = 'author@example.com';
            $category = $item->add( 'category' );
            $category->term = self::TEXT;
        }

        if ( $type === 'atom' )
        {
            $item->content = self::TEXT;
        }

        return $feed;
    }

    /**
     * The text of the single node $query finds in $xml.
     *
     * @param string $xml
     * @param string $query
     * @return string
     */
    protected function text( $xml, $query )
    {
        $doc = new DOMDocument();
        $this->assertTrue( $doc->loadXML( $xml ), 'The generated feed is well-formed XML' );
        $xpath = new DOMXPath( $doc );
        $xpath->registerNamespace( 'atom', 'http://www.w3.org/2005/Atom' );
        $xpath->registerNamespace( 'rss1', 'http://purl.org/rss/1.0/' );
        $xpath->registerNamespace( 'dc', 'http://purl.org/dc/elements/1.1/' );
        $xpath->registerNamespace( 'content', 'http://purl.org/rss/1.0/modules/content/' );
        $xpath->registerNamespace( 'itunes', 'http://www.itunes.com/dtds/podcast-1.0.dtd' );
        $xpath->registerNamespace( 'creativeCommons', 'http://backend.userland.com/creativeCommonsRssModule' );
        $nodes = $xpath->query( $query );
        $this->assertSame( 1, $nodes->length, "One node for {$query}" );

        return $nodes->item( 0 )->textContent;
    }

    public static function getTextFields()
    {
        return array(
            'rss1 title'                => array( 'rss1', '/rdf:RDF/rss1:channel/rss1:title', self::TEXT ),
            'rss1 description'          => array( 'rss1', '/rdf:RDF/rss1:channel/rss1:description', self::TEXT ),
            'rss1 item title'           => array( 'rss1', '/rdf:RDF/rss1:item/rss1:title', self::TEXT ),
            'rss1 item description'     => array( 'rss1', '/rdf:RDF/rss1:item/rss1:description', self::TEXT ),

            'rss2 title'                => array( 'rss2', '/rss/channel/title', self::TEXT ),
            'rss2 description'          => array( 'rss2', '/rss/channel/description', self::TEXT ),
            'rss2 copyright'            => array( 'rss2', '/rss/channel/copyright', self::TEXT ),
            'rss2 managingEditor'       => array( 'rss2', '/rss/channel/managingEditor', 'author@example.com (' . self::TEXT . ')' ),
            'rss2 category'             => array( 'rss2', '/rss/channel/category', self::TEXT ),
            'rss2 generator'            => array( 'rss2', '/rss/channel/generator', self::TEXT ),
            'rss2 item title'           => array( 'rss2', '/rss/channel/item/title', self::TEXT ),
            'rss2 item description'     => array( 'rss2', '/rss/channel/item/description', self::TEXT ),
            'rss2 item author'          => array( 'rss2', '/rss/channel/item/author', 'author@example.com (' . self::TEXT . ')' ),
            'rss2 item category'        => array( 'rss2', '/rss/channel/item/category', self::TEXT ),
            'rss2 item guid'            => array( 'rss2', '/rss/channel/item/guid', 'http://example.com/?i=1&j=2' ),

            'atom title'                => array( 'atom', '/atom:feed/atom:title', self::TEXT ),
            'atom subtitle'             => array( 'atom', '/atom:feed/atom:subtitle', self::TEXT ),
            'atom rights'               => array( 'atom', '/atom:feed/atom:rights', self::TEXT ),
            'atom author name'          => array( 'atom', '/atom:feed/atom:author/atom:name', self::TEXT ),
            'atom category'             => array( 'atom', '/atom:feed/atom:category/@term', self::TEXT ),
            'atom generator'            => array( 'atom', '/atom:feed/atom:generator', self::TEXT ),
            'atom entry title'          => array( 'atom', '/atom:feed/atom:entry/atom:title', self::TEXT ),
            'atom entry summary'        => array( 'atom', '/atom:feed/atom:entry/atom:summary', self::TEXT ),
            'atom entry content'        => array( 'atom', '/atom:feed/atom:entry/atom:content', self::TEXT ),
            'atom entry author name'    => array( 'atom', '/atom:feed/atom:entry/atom:author/atom:name', self::TEXT ),
        );
    }

    /**
     * @dataProvider getTextFields
     */
    #[PHPUnit\Framework\Attributes\DataProvider( 'getTextFields' )]
    public function testTextIsEscapedOnce( $type, $query, $expected )
    {
        $xml = $this->createFeed( $type )->generate( $type );

        $this->assertSame( $expected, $this->text( $xml, $query ) );
    }

    public static function getTypes()
    {
        return array( 'rss1' => array( 'rss1' ), 'rss2' => array( 'rss2' ), 'atom' => array( 'atom' ) );
    }

    /**
     * Generating changed the feed: the html and text/* content of ATOM was
     * escaped into the element itself, so a second generate() escaped it again.
     *
     * @dataProvider getTypes
     */
    #[PHPUnit\Framework\Attributes\DataProvider( 'getTypes' )]
    public function testGenerateTwiceGivesTheSameFeed( $type )
    {
        $feed = $this->createFeed( $type, $item );
        if ( $type === 'atom' )
        {
            $feed->title->type = 'html';
            $item->content->type = 'text/plain';
        }

        $this->assertSame( $feed->generate( $type ), $feed->generate( $type ) );
    }

    public static function getAtomTextTypes()
    {
        return array(
            'text'       => array( 'text' ),
            'html'       => array( 'html' ),
            'text/plain' => array( 'text/plain' ),
        );
    }

    /**
     * The html type carries markup as escaped text: the element's text is the
     * markup itself, the same as for text.
     *
     * @dataProvider getAtomTextTypes
     */
    #[PHPUnit\Framework\Attributes\DataProvider( 'getAtomTextTypes' )]
    public function testAtomContentTypes( $contentType )
    {
        $feed = $this->createFeed( 'atom', $item );
        $item->content->type = $contentType;
        if ( $contentType !== 'text/plain' )
        {
            $feed->title->type = $contentType;
        }
        $xml = $feed->generate( 'atom' );

        $this->assertSame( self::TEXT, $this->text( $xml, '/atom:feed/atom:entry/atom:content' ) );
        $this->assertSame( self::TEXT, $this->text( $xml, '/atom:feed/atom:title' ) );
    }

    /**
     * @dataProvider getTypes
     */
    #[PHPUnit\Framework\Attributes\DataProvider( 'getTypes' )]
    public function testParseGivesBackTheText( $type )
    {
        $parsed = ezcFeed::parseContent( $this->createFeed( $type )->generate( $type ) );

        $this->assertSame( self::TEXT, $parsed->title->__toString() );
        $this->assertSame( self::TEXT, $parsed->item[0]->title->__toString() );
        $this->assertSame( self::TEXT, $parsed->item[0]->description->__toString() );
        if ( $type !== 'rss1' )
        {
            $this->assertSame( self::TEXT, $parsed->category[0]->term );
            $this->assertSame( self::TEXT, $parsed->generator->name );
            $this->assertSame( self::TEXT, $parsed->item[0]->category[0]->term );
        }
    }

    public function testModules()
    {
        $feed = $this->createFeed( 'rss2', $item );

        $dc = $item->addModule( 'DublinCore' );
        $creator = $dc->add( 'creator' );
        $creator->name = self::TEXT;

        $content = $item->addModule( 'Content' );
        $content->encoded = self::TEXT;

        $itunes = $item->addModule( 'iTunes' );
        $itunes->summary = self::TEXT;

        $cc = $item->addModule( 'CreativeCommons' );
        $cc->license = 'http://example.com/license?a=1&b=2';

        $xml = $feed->generate( 'rss2' );

        $this->assertSame( self::TEXT, $this->text( $xml, '/rss/channel/item/dc:creator' ) );
        $this->assertSame( self::TEXT, $this->text( $xml, '/rss/channel/item/content:encoded' ) );
        $this->assertSame( self::TEXT, $this->text( $xml, '/rss/channel/item/itunes:summary' ) );
        $this->assertSame( 'http://example.com/license?a=1&b=2', $this->text( $xml, '/rss/channel/item/creativeCommons:license' ) );
    }
}
?>
