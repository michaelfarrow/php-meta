<?php
/**
 * Utility class to provide meta tags for search engines and
 * social networks including Twitter Cards and Facebook OpenGraph.
 * Will make recommendations as to which tags are required and
 * recommended, and will attempt to automatically decide which
 * Twitter Card type should be chosen.
 *
 * @author    Mike Farrow <contact@mikefarrow.co.uk>
 * @license   Proprietary/Closed Source
 * @copyright Mike Farrow
 */

namespace Weyforth\Meta;

class Meta
{

	protected static $title;
	protected static $description;
	protected static $image;
	protected static $twitterCardsImage;
	protected static $openGraphImage;
	protected static $images;
	protected static $video;
	protected static $videoWidth = 1920;
	protected static $videoHeight = 1080;
	protected static $embed;
	protected static $url;
	protected static $twitterSite;
	protected static $twitterCreator;
	protected static $type;
	protected static $siteName;

	protected static $normalEnabled = true;
	protected static $twitterCardsEnabled = true;
	protected static $openGraphEnabled = true;

	protected static $twitterPreferLargeImage = false;

	protected static $twitterCardType;

	protected static $validTwitterCardTypes = array(
		'summary',
		'summary_large_image',
		'player',
		'gallery',
	);

	protected static $requiredNormal = array(
		'title',
		'description',
	);

	protected static $recommendedNormal = array(
		'keywords',
	);

	protected static $requiredOpenGraph = array(
		'og:title',
		'og:type',
		'og:url',
	);

	protected static $recommendedOpenGraph = array(
		'og:image',
		'og:description',
		'og:site_name',
	);

	protected static $requiredTwitterGlobal = array(
		'twitter:card',
	);

	protected static $recommendedTwitterGlobal = array(

	);

	protected static $rulesTwitterCard = array(
		'summary' => array(
			'required' => array(
				'twitter:title',
				'twitter:description',
			),
			'recommended' => array(
				'twitter:image',
			),
		),
		'summary_large_image' => array(
			'required' => array(
				'twitter:title',
				'twitter:description',
			),
			'recommended' => array(
				'twitter:image',
			),
		),
		'player' => array(
			'required' => array(
				'twitter:player',
				'twitter:player:width',
				'twitter:player:height',
				'twitter:title',
				'twitter:description',
				'twitter:image',
			),
			'recommended' => array(

			),
		),
		'gallery' => array(
			'required' => array(
				'twitter:image0',
				'twitter:image1',
				'twitter:image2',
				'twitter:image3',
			),
			'recommended' => array(
				'twitter:title',
				'twitter:description',
			),
		)
	);

	public static function title( $title )
	{
		self::$title = $title;
	}

	public static function description( $description )
	{
		self::$description = $description;
	}

	public static function type( $type )
	{
		self::$type = $type;
	}

	public static function siteName( $siteName )
	{
		self::$siteName = $siteName;
	}

	public static function image( $image )
	{
		self::$image = $image;
	}

	public static function twitterCardsImage( $image )
	{
		self::$twitterCardsImage = $image;
	}

	public static function openGraphImage( $image )
	{
		self::$openGraphImage = $image;
	}

	public static function images( $images )
	{
		self::$images = $images;
	}

	public static function video( $video )
	{
		self::$video = $video;
	}

	public static function videoWidth( $videoWidth )
	{
		self::$videoWidth = $videoWidth;
	}

	public static function videoHeight( $videoHeight )
	{
		self::$videoHeight = $videoHeight;
	}

	public static function embed( $embed )
	{
		self::$embed = $embed;
	}

	public static function url( $url )
	{
		self::$url = $url;
	}

	public static function twitterSite( $twitterSite )
	{
		self::$twitterSite = $twitterSite;
	}

	public static function twitterCreator( $twitterCreator )
	{
		self::$twitterCreator = $twitterCreator;
	}

	public static function twitterPreferLargeImage()
	{
		self::$twitterPreferLargeImage = true;
	}

	public static function twitterPreferSmallImage()
	{
		self::$twitterPreferLargeImage = false;
	}

	public static function disableNormal()
	{
		self::$normalEnabled = false;
	}

	public static function enableNormal()
	{
		self::$normalEnabled = true;
	}

	public static function disableTwitterCards()
	{
		self::$twitterCardsEnabled = false;
	}

	public static function enableTwitterCards()
	{
		self::$twitterCardsEnabled = true;
	}

	public static function disableOpenGraph()
	{
		self::$openGraphEnabled = false;
	}

	public static function enableOpenGraph()
	{
		self::$openGraphEnabled = true;
	}

	public static function twitterCardType($type)
	{
		self::$twitterCardType = in_array($type, self::$validTwitterCardTypes) ? $type : null;
	}

	protected static function convertVideoToEmbed($url)
	{
		$found = true;

		if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
	        $meta_video_frame = 'https://www.youtube.com/embed/' . $match[1];
	        $meta_video_swf = 'http://www.youtube.com/v/' . $match[1] . '?autohide=1&amp;version=3';
	        $meta_video_swf_secure = 'https://www.youtube.com/v/' . $match[1] . '?autohide=1&amp;version=3';
	    }else if(preg_match('%https?:\/\/.*vimeo\.com/(\w*/)*(\d+)%i', $url, $match)){
	        $meta_video_frame = 'https://player.vimeo.com/video/' . $match[2];
	        $meta_video_swf = 'http://vimeo.com/moogaloop.swf?clip_id=' . $match[2];
	        $meta_video_swf_secure = 'https://vimeo.com/moogaloop.swf?clip_id=' . $match[2];
	    }else{
	    	$found = false;
	    }

	    return $found ? (object) array(
	    	'frame' => $meta_video_frame,
	    	'swf' => $meta_video_swf,
	    	'swf_secure' => $meta_video_swf_secure,
	    ) : false;
	}

	protected static function comment( $string )
	{
		return "<!-- " . $string . " -->\n";
	}

	protected static function checkTags( $req, $rec, $data )
	{
		$data = array_filter( $data );
		$warnings = array();

		foreach( $req as $name ){
			if( ! array_key_exists($name, $data) ){
				$warnings[] = static::comment( 'Required: ' . $name );
			}
		}

		foreach( $rec as $name ){
			if( ! array_key_exists($name, $data) ){
				$warnings[] = static::comment( 'Recommended: ' . $name );
			}
		}

		return count($warnings) == 0 ? '' : join('', $warnings);
	}

	protected static function inferTwitterCardType()
	{
		if(!self::$twitterCardType){
			$video = self::convertVideoToEmbed(self::$video);
			if($video){
				return 'player';
			}else if(self::$images && count(self::$images) == 4){
				return 'gallery';
			}
			return self::$twitterPreferLargeImage ? 'summary_large_image' : 'summary';
		}else{
			return self::$twitterCardType;
		}
	}

	protected static function inferUrl()
	{
		if(!self::$url){
			$pageURL = 'http';
			if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on") $pageURL .= "s";

			$pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} else {
				$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}

			return $pageURL;
		}else{
			return self::$url;
		}
	}

	protected static function formatTwitterUsername($name)
	{
		return $name ? '@' . $name : null;
	}

	protected static function expandTag( $key, $value, $prop ){
		if($key == 'og:image' && is_array($value) && count($value) == 3){
			$out = '';

			$out .= "<meta ".($prop ? 'property' : 'name')."=\"".$key."\" content=\"".str_replace('"', '&quot;',$value[0])."\" />\n";
			$out .= "<meta ".($prop ? 'property' : 'name')."=\"".$key.":width\" content=\"".str_replace('"', '&quot;',$value[1])."\" />\n";
			$out .= "<meta ".($prop ? 'property' : 'name')."=\"".$key.":height\" content=\"".str_replace('"', '&quot;',$value[2])."\" />\n";

			return $out;
		}

		return "<meta ".($prop ? 'property' : 'name')."=\"".$key."\" content=\"".str_replace('"', '&quot;',$value)."\" />\n";
	}

	protected static function outputTag( $key, $value, $prop = false )
	{
		$out = '';
		if($value && is_array($value)){
			foreach ($value as $sub) {
				$out .= self::expandTag($key, $sub, $prop);
			}
			return $out;
		}

		return self::expandTag($key, $value, $prop);
	}

	protected static function outputTags( $title, $req, $rec, $tags, $prop = false )
	{
		$out = "\n";
		$out .= self::comment( strtoupper( $title ) );
		$out .= self::checkTags( $req, $rec, $tags );

		$tags_non_empty = array_filter( $tags );
		$tags_empty = array_diff_key( $tags, $tags_non_empty );

		foreach( $tags_non_empty as $key => $value ){
			$out .= self::outputTag( $key, $value, $prop );
		}

		return $out;
	}

	public static function getOutput()
	{
		$out = '';
		$data = array();
		$url = self::inferUrl();
		$video = self::convertVideoToEmbed(self::$video);

		if(self::$normalEnabled){
			$data['title'] = self::$title;
			$data['description'] = self::$description;
		}

		$out .= self::outputTags( 'normal', self::$requiredNormal, self::$recommendedNormal, $data );

		$data = array();

		if(self::$twitterCardsEnabled){
			$type = self::inferTwitterCardType();

			$data['twitter:card'] = $type;
			$data['twitter:title'] = self::$title;
			$data['twitter:description'] = self::$description;
			$data['twitter:url'] = $url;

			if(self::$twitterCardsImage){
				if(is_array(self::$twitterCardsImage)){
					$data['twitter:image'] = self::$twitterCardsImage[0];
					$data['twitter:image:width'] = self::$twitterCardsImage[1];
					$data['twitter:image:height'] = self::$twitterCardsImage[2];
				}else{
					$data['twitter:image'] = self::$twitterCardsImage;
				}
			}else{
				if(is_array(self::$image)){
					$data['twitter:image'] = self::$image[0];
					$data['twitter:image:width'] = self::$image[1];
					$data['twitter:image:height'] = self::$image[2];
				}else{
					$data['twitter:image'] = self::$image;
				}
			}
			

			switch ($type) {
				case 'player':
					$data['twitter:player:width'] = self::$videoWidth;
					$data['twitter:player:height'] = self::$videoHeight;
					$data['twitter:player'] = $video->frame;
					break;

				case 'gallery':
					if(self::$images && count(self::$images) == 4){
						$data['twitter:image0'] = self::$images[0];
						$data['twitter:image1'] = self::$images[1];
						$data['twitter:image2'] = self::$images[2];
						$data['twitter:image3'] = self::$images[3];
					}
					unset($data['twitter:image']);
					break;
			}

			$data['twitter:site'] = self::formatTwitterUsername(self::$twitterSite);
			$data['twitter:creator'] = self::formatTwitterUsername(self::$twitterCreator);

			$out .= self::outputTags( 'twitter', array_merge(self::$requiredTwitterGlobal, self::$rulesTwitterCard[$type]['required']), array_merge(self::$recommendedTwitterGlobal, self::$rulesTwitterCard[$type]['recommended']), $data );
		}

		$data = array();

		if(self::$openGraphEnabled){
			$data['og:type'] = self::$type;
			$data['og:url'] = $url;
			$data['og:site_name'] = self::$siteName;
			$data['og:title'] = self::$title;
			$data['og:description'] = self::$description;

			if(self::$images && is_array(self::$images)){
				if(self::$image) $data['og:image'][] = self::$image;

				foreach (self::$images as $image) {
					$data['og:image'][] = $image;
				}
			}else{
				if(self::$openGraphImage){
					$data['og:image'] = self::$openGraphImage;
				}else{
					$data['og:image'] = self::$image;
				}
			}

			if($video){
				$data['og:video:type'] = 'application/x-shockwave-flash';
				$data['og:video:width'] = self::$videoWidth;
				$data['og:video:height'] = self::$videoHeight;
				$data['og:video'] = $video->swf;
				$data['og:video:secure_url'] = $video->swf_secure;
			}

			$out .= self::outputTags( 'facebook', self::$requiredOpenGraph, self::$recommendedOpenGraph, $data, true );
		}


		return $out;
	}

	public static function output()
	{
		print static::getOutput();
	}

}
