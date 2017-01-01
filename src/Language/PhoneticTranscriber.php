<?php

namespace GGG\Language;

use Requests;

class PhoneticTranscriber
{

	const API_URL = 'http://www.lingorado.com/ipa/';

	private $headers;
	private $data;

	public function __construct()
	{
		$this->data = [
		   /* 
			* Text from which to be transcribed
			*
			* default: NULL
			*/
			'text_to_transcribe' => NULL,
		  
		   /* 
			* Submit button text value
			*
			* default: 'Show transcription'
			*/
			'submit' => 'Show transcription',
		  
		   /* 
			* Transcribe using specified dialect
			* 
			* 'br' (british)
			* 'am' (american)
			*
			* default: 'am'
			*/
			'output_dialect' => 'am',
		  
		   /* 
			* Transcription output style
			* 
			* 'text' (text only),
			* 'columns' (side by side),
			* 'inline' (line by line)
			*
			* default: 'inline'
			*/
			'output_style' => 'inline',
		  
		   /* 
			* How to show weak linguistic forms
			*
			* default: ''
			*/
			'weak_forms' => '',
		  
		   /*
			* Character to use pre-bracket
			*
			* default: ''
			*/
			'preBacket' => '',

		   /* 
			* character to use post-bracket
			*
			* default: ''
			*/
			'postBracket' => '',
		
		   /* 
			* Add speech support?
			*
			* default: '0'
			*/
			'speech_support' => '0'
		];

		$this->headers = [
			'Referer' => static::API_URL
		];
	}

	public function set( $property, $value )
	{
		// set $this->data['text']
		if( preg_match( '/^text[a-z\_]{0,14}$/i', $property ) ) 
		{
			$this->data['text_to_transcribe'] = (string) strip_tags( trim( $value ) );
		}
		// set $this->data['ouput_dialect']
		if( preg_match( '/^(output)?[\_]?dialect$/i', $property ) ) 
		{
			if( preg_match( '/^br(itish)?$/i', $value ) )
			{
				$this->data['output_dialect'] = 'br';
			}
			if( preg_match( '/^am(erican)?$/i', $value ) )
			{
				$this->data['output_dialect'] = 'am';
			}
		}
		// set $this->data['output_style']
		if( preg_match( '/^(output)?[\_]?style$/i', $property ) ) 
		{
			if( preg_match( '/^te?(xt)?$/i', $value ) )
			{
				$this->data['output_style'] = 'text';
			}
			if( preg_match( '/^col(umn)?s?$/i', $value ) )
			{
				$this->data['output_style'] = 'columns';
			}
			if( preg_match( '/^in(line)?$/i', $value ) )
			{
				$this->data['output_style'] = 'inline';
			}
		}
		// set $this->data['weak_forms']
		if( preg_match( '/^weak[\_]?(forms?)?$/i', $property ) ) 
		{
			$this->data['weak_forms'] = ( $value === true || $value === 1 ) ? '1' : '';
		}
		// set $this->data['speech_support']
		if( preg_match( '/^speech[\_]?(support?)?$/i', $property ) ) 
		{
			$this->data['speech_support'] = ( $value === true || $value === 1 ) ? '1' : '0';
		}
		return $this;
	}

	private function getTranscription()
	{
		$response = Requests::post( static::API_URL, $this->headers, $this->data );
		return $response->body;
	}

	public function transcribe( $text = NULL )
	{
		$resultText = 'Error';
		if( $this->data['text_to_transcribe'] === NULL && isset( $text ) && is_string( $text ) && strlen( $text ) >= 1 )
		{
			$this->data['text_to_transcribe'] = (string) strip_tags( trim( $text ) );
		}
		if( isset( $this->data['text_to_transcribe'] ) && is_string( $this->data['text_to_transcribe'] ) && strlen( $this->data['text_to_transcribe'] ) >= 1 )
		{
			$transcription = $this->getTranscription();
			preg_match_all( '/.+?\<span\s+class\=\"transcribed[\_]word\"\>(<a.+?\>)?(.+)(\<\/a\>)?\<\/span\>.+?/i', $transcription, $matches );
			if( $matches[2] ) 
			{
				switch( $this->data['output_style'] )
				{
					case 'text':
						$resultText = $matches[2];
						break;
					case 'columns':
						$resultText = $this->data['text_to_transcribe'] . '   ' . $matches[2][0];
						break;
					case 'inline':
						$resultText = $this->data['text_to_transcribe'] . "<br>\n" . $matches[2][0];
						break;
				}
			}
			else 
			{
				$resultText = 'No Transcription Found.';
			}
			return $resultText;
		}
		return 'No input text provided.';
	}

}
