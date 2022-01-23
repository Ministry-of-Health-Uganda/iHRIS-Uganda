<?php
/**
 * @copyright © 2007, 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
    * @package I2CE
    * @author Sovello Hildebrand <sovellohpmgani@gmail.com>
    * @since v4.1
    * @version v4.1.0
    */
/**
 * Class defining the passport field.
 * @package I2CE
 * @access public
 */
class I2CE_FormField_PASSPORT extends I2CE_FormField_IMAGE {
    
    
    
    /** 
     * Sets the value of this field from the posted form.
     * @param mixed $post
     */
    public function setFromData($data,$file_name,$mime_type=false, $fmod_time = false) { 
        //create a temp file to store the $data into and then resize the image
        $res = tempnam('/tmp', 'i2ce_img_');
        file_put_contents( $res, $data ); //copy $data into $res temporary file
        list($img_width, $img_height, $img_type, $other_attrs) = getimagesize( $res );
        
        if( ($img_type = exif_imagetype( $res )) === false){
            I2CE::raiseError( "The image seems to be corrupt!");
          }
        
        /* get the max width and height if specified in the xml file otherwise, we default to original image dimensions */
        $m_height = $this->getOptionsByPath('meta/max_height');
        $m_width = $this->getOptionsByPath('meta/max_width');
        $max_height = !empty( $m_height ) ?  $m_height : $img_height;
        $max_width = !empty( $m_width ) ? $m_width : $img_width;
        
        if( $img_width == 0 || $img_height == 0 ){
          I2CE::raiseError( "Height or width can not be zero"); //just to avoided division by zero
        }
        else{
          //check if the image dimensions don't exceed the maximum dimensions specified, don't do anything. otherwise resize
          if( $img_width < $max_width && $img_height < $max_height ){
            //there is nothing to resize: image size within limits
          }
          else{
            //calculating the new height and new width
            $new_height = '';
            $new_width = '';
            if( $img_height < $img_width ){
              $new_width = $max_width;
              $ratio = $img_height / $img_width;
              $new_height = floor($max_width * $ratio);
            }
            elseif( $img_height > $img_width ){
              $new_height = $max_height;
              $ratio = $img_width / $img_height;
              $new_width = floor($max_height * $ratio);
            }
            else{
              //the image is a square, resize it to the maximum dimensions specified
              $new_height = $max_height;
              $new_width = $max_width;
            }
            switch ( $img_type ){

              case 1:
                $img = imagecreatefromgif( $res );
                $ppt = $this->imageToPassport($img, $new_width, $new_height, $img_width, $img_height );
                break;
              case 2:
                $img = imagecreatefromjpeg( $res );
                $ppt = $this->imageToPassport($img, $new_width, $new_height, $img_width, $img_height );
                break;
              case 3:
                $img = imagecreatefrompng( $res );
                $ppt = $this->imageToPassport($img, $new_width, $new_height, $img_width, $img_height );
                break;
              case 15:
                $img = imagecreatefromwbmp( $res );
                $ppt = $this->imageToPassport($img, $new_width, $new_height, $img_width, $img_height );
                break;
              case 16:
                $img = imagecreatefromxbm( $res );
                $ppt = $this->imageToPassport($img, $new_width, $new_height, $img_width, $img_height );
                break;
              case 6:
                $img = $this->imagecreatefrombmp( $res );
                $ppt = $this->imageToPassport($img, $new_width, $new_height, $img_width, $img_height );
                break;
              default:
                break;
            }
            //$copy the new resized image into $data
            $data = file_get_contents( $ppt );
            unlink($ppt);            
          }             
          unlink($res); //delete all temporary stuff
        }        
      /* insert here the logic from lines 88-157 */
      parent::setFromData($data,$file_name,$mime_type,$fmod_time);
    }
    
    /** 
     * Resizes the image to a passport size specified
     * @param resource $image the holder for the new image to be created
     * @param int $n_width, $n_height, $width, $height image dimensions: new height and width and original image width and height respectively
     */
    protected function imageToPassport($image, $n_width, $n_height, $width, $height){
      $passport = tempnam('/tmp', 'i2ce_thm_'); //new thumbnail be copied here
      $img_holder = imagecreatetruecolor( $n_width, $n_height );
      imagecopyresampled($img_holder, $image, 0, 0, 0, 0, $n_width, $n_height, $width, $height);
      imagejpeg($img_holder, $passport);
      return $passport;
    }
    
    /**
     * .bmp is just hex encoded RGB values.
     * All you need to do is open in binary mode and seperate the header from the body.
     * Decode the width and height from the header. 
     * Then create the image pixel by pixel from the RGB values in the body. 
     * source: http://www.php.net/manual/en/function.imagecreatefromwbmp.php
     * 
     */
     
     protected function imagecreatefrombmp( $filename ){
      $file = fopen( $filename, "rb" );
      $read = fread( $file, 10 );
      while( !feof( $file ) && $read != "" )
      {
          $read .= fread( $file, 1024 );
      }
      $temp = unpack( "H*", $read );
      $hex = $temp[1];
      $header = substr( $hex, 0, 104 );
      $body = str_split( substr( $hex, 108 ), 6 );
      if( substr( $header, 0, 4 ) == "424d" )
      {
          $header = substr( $header, 4 );
          // Remove some stuff?
          $header = substr( $header, 32 );
          // Get the width
          $wh = hexdec( substr( $header, 0, 2 ) );
          // Remove some stuff?
          $header = substr( $header, 8 );
          // Get the height
          $ht = hexdec( substr( $header, 0, 2 ) );
          unset( $header );
      }
      $x = 0;
      $y = 1;
      list($width, $height) = getimagesize($filename); //fetch the width and height from getimagesize(), as the method in lines 157 and 161 above was not very accurate
      $image = imagecreatetruecolor( $width, $height );
      foreach( $body as $rgb )
      {
          $r = hexdec( substr( $rgb, 4, 2 ) );
          $g = hexdec( substr( $rgb, 2, 2 ) );
          $b = hexdec( substr( $rgb, 0, 2 ) );
          $color = imagecolorallocate( $image, $r, $g, $b );
          imagesetpixel( $image, $x, $height-$y, $color );
          $x++;
          if( $x >= $width )
          {
              $x = 0;
              $y++;
          }
      }
      return $image;
  }
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
