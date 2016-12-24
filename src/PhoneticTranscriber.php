<?php

namespace GGG;

class PhoneticTranscriber
{

  const API_URL = 'http://www.lingorado.com/ipa/';

  private $headers;
  private $data;

  public function __construct( $text = '' )
  {
    $this->data = [
      // TEXTAREA
      // text to be transcribed
      //
      // default: ''
      'text_to_transcribe' => $text,
      
      // SUBMIT BUTTON
      //
      // default: 'Show transcription'
      'submit' => 'Show transcription',
      
      // RADIO GROUP
      // transcribe using dialect
      // choices are:
      // 'br' (british),
      // 'am' (american)
      //
      // default: ''
      'output_dialect' => 'am',
      
      // RADIO GROUP
      // transcription output style
      // choices are:
      // 'only_tr' (transcription only),
      // 'columns' (side by side),
      // 'inline' (line by line)
      //
      // default: 'inline'
      'output_style' => 'inline',
      
      // CHECKBOX 
      // show weak forms
      //
      // default: ''
      'weak_forms' => '1',
      
      // TEXTBOX
      // character to use for preBracket
      //
      // default: ''
      'preBracket' => '',

      // TEXTBOX
      // character to use for postBracket
      //
      // default: ''
      'postBracket' => '',
      
      // HIDDEN
      // add speech support
      //
      // default: '0'
      'speech_support' => '0'
    ];
    $this->headers = [
      'Referer' => static::API_URL
    ];
  }

  public function set( $property, $value )
  {
    if( preg_match( '/^text[a-z\_]{0,14}$/i', $property ) ) 
    {
      $this->data['text_to_transcribe'] = (string) strip_tags( $value );
    }
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
    if( preg_match( '/^(output)?[\_]?style$/i', $property ) ) 
    {
      if( preg_match( '/^(only)?[\_]?tr$/i', $value ) )
      {
        $this->data['output_style'] = 'only_tr';
      }
      if( preg_match( '/^col(umns?)?$/i', $value ) )
      {
        $this->data['output_style'] = 'columns';
      }
      if( preg_match( '/^in(line)?$/i', $value ) )
      {
        $this->data['output_style'] = 'inline';
      }
    }
    if( preg_match( '/^weak[\_]?(forms?)?$/i', $property ) ) 
    {
      $this->data['weak_forms'] = ( $value === true || $value === 1 ) ? '1' : '';
    }
    if( preg_match( '/^speech[\_]?(support?)?$/i', $property ) ) 
    {
      $this->data['speech_support'] = ( $value === true || $value === 1 ) ? '1' : '';
    }
    return $this;
  }

  private function getTranscription()
  {
    $response = \Requests::post( static::API_URL, $this->headers, $this->data );
    return $response->body;
  }

  public function transcribe( $show_html = false )
  {
    $resultText = 'Error';
    $transcription = $this->getTranscription();
    preg_match_all( '/.+?\<span\s+class\=\"transcribed[\_]word\"\>(<a.+?\>)?(.+)(\<\/a\>)?\<\/span\>.+?/i', $transcription, $matches );
    if( $matches[2] ) 
    {
      switch( $this->data['output_style'] )
      {
        case 'tr_only':
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
    else {
      $resultText = 'No Transcription Found.';
    }
    if( $show_html === false )
    {
      return $resultText;
    }
    if( $show_html === true )
    {
      print_r( $transcription );
    }
  }

}