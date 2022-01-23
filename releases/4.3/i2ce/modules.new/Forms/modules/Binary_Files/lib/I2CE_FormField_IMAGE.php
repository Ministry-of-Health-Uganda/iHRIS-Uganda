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
    * @author Carl Leitner <litlfred@ibiblio.org>
    * @since v2.0.0
    * @version v2.0.0
    */
/**
 * Class for defining all the database fields used by a {@link I2CE_Form} object.
 * @package I2CE
 * @access public
 */
class I2CE_FormField_IMAGE extends I2CE_FormField_BINARY_FILE {
    /**
     * Checks to see if a mime type is a valid image mime type
     * @param string $mime_type The mime type we are varifying is valid
     * @returns true if valid.  false otherwise
     */
    public function isValidMimeType($mime_type) {
        return preg_match('/^image/',$mime_type);
    }

    public function setFromPost( $post ) {
        //webcam key should follow http://tools.ietf.org/html/rfc2397  
        if (array_key_exists('webcam',$post)
            && is_string($rawdata = $post['webcam'])
            && substr($rawdata,0,5) == 'data:'
            ) {
            $rawdata = substr($rawdata,5);
            list($mediaencoding,$data) = array_pad(explode(',',$rawdata,2),2,'');
            $mediaencoding = explode(';',$mediaencoding);
            $encoding = 'base64';
            $params = array();
            $mediatype = false;
            foreach ($mediaencoding as $part) {
                if (strpos($part,'/') !== false) {
                    //it is a media type
                    $mediatype = $part;
                } else if (strpos($part,'=') !== false) {
                    //it is a paramter;
                    $params[]  = $part;
                } else {
                    //it is an encoding
                    $encoding = $part;
                }                
            }
            if (!$mediatype) {
                $mediatype = 'text/plain;charset=US-ASCII';
            } else {
                if (count($params) > 0) {
                    $mediatype .= ';' . implode(';',$params);
                }
            }
            if ($encoding == 'base64') {
                $data = base64_decode($data);
            } else {
                //using ASCII encoding for octets inside the   range of safe URL characters and using the standard %xx hex encoding   of URLs for octets outside that range. 
                $data = urldecode($data);
            }
            $this->setFromData($data,'webcam.png',$mediatype);
            if (!$this->tmp_key) {
			       $this->setTempKey(md5($this->name . $name . rand(0,100000)));
			}
            $this->storeInTemporaryTable(); 
        } else {
            parent::setFromPost($post);
        }

    }


    public function processDOMEditable($node,$template,$form_node) {
        $ele_name = $this->getHTMLName();
        $link = $this->getLink();
        if ($link && strlen( $this->value ) > 0 ) {
            $text = $this->getDisplayValue();
            $attrs = array("src"=>$link,'alt'=>'image','class'=>'field_image');
            $node->appendChild($template->createElement('img', $attrs));
            $node->appendChild($template->createElement('br'));
        }
        if ($this->tmp_key) {
            $node->appendChild($template->createElement( "input", array( "name" => $ele_name . '[tmp_key]' , "type"=>"hidden", 'value' => $this->tmp_key)));
        }
        $node->appendChild($template->createElement( "input", array( "name" => $ele_name . '[file]' , "type"=>"file", 'size' => 40)));
        $node->appendChild($template->createElement( "input", array( "name" => $ele_name . '[exists]' , "type"=>"hidden", 'value' => 1)));

        //we need to add a hidden input  node so that $this->setFromPost() is triggeredd  from $form->setFromPost()

        //now try and do webcam capture
        $capture_attrs = array(
            'width' => 640,
            'height' => 480,
            'autoplay'=>1,
            'id'=>($capture_id = $ele_name.'[capture]')
            );
        if ($this->optionsHasPath("meta/webcam/preview_width")) {
            $capture_attrs['width'] = max(0,(int) $this->getOptionsByPath("meta/webcam/capture_width"));
        }
        if ($this->optionsHasPath("meta/webcam/preview_height")) {
            $capture_attrs['height'] = max(0,(int) $this->getOptionsByPath("meta/webcam/capture_height"));
        }
        
        $video_attrs = array(
            'width' => 128,
            'height' => 96,
            'autoplay'=>1,
            'id'=>($video_id = $ele_name.'[video]')
            );
        if ($this->optionsHasPath("meta/webcam/preview_width")) {
            $video_attrs['width'] = max(0,(int) $this->getOptionsByPath("meta/webcam/video_width"));
        }
        if ($this->optionsHasPath("meta/webcam/preview_height")) {
            $video_attrs['height'] = max(0,(int) $this->getOptionsByPath("meta/webcam/video_height"));
        }
        $snap_text= "Snap Picture";
        I2CE::getConfig()->setIfIsSet($snap_text,"/modules/forms/messags/snap_picture");
        $button_attrs = array(
            'id'=>($snap_id = $ele_name . '[snap]'),
            'onclick'=>'return false;'
            );
        

        $node->appendChild($template->createElement('br'));
        $node->appendChild($webcamNode = $template->createElement('span',array('class'=>'field_webcam')));
        $webcamNode->appendChild($webcamVideoNode = $template->createElement('span',array('class'=>'field_webcam_video')));
        $webcamVideoNode->appendChild( $template->createElement('video',$video_attrs));
        $webcamVideoNode->appendChild( $template->createElement('button',$button_attrs,$snap_text));
        $webcamNode->appendChild($webcamCaptureNode = $template->createElement('span',array('class'=>'field_webcam_capture')));
        $webcamCaptureNode->appendChild( $template->createElement('canvas',$capture_attrs));
        $webcam_id = $ele_name . '[webcam]';
        $webcamCaptureNode->appendChild($template->createElement( "input", array( "name" => $webcam_id,'id'=>$webcam_id, "type"=>"hidden" )));
        
        $js = "
	var canvas = document.getElementById('$capture_id'),
	    context = canvas.getContext('2d'),
	    video = document.getElementById('$video_id'),
	    videoObj = { 'video': true },
	    errBack = function(error) {
		console.log('Video capture error: ', error.code); 
	    };

	    // Put video listeners into place
	    if(navigator.getUserMedia) { // Standard
		navigator.getUserMedia(videoObj, function(stream) {
			video.src = stream;
			video.play();
		    }, errBack);
	    } else if(navigator.webkitGetUserMedia) { // WebKit-prefixed
		navigator.webkitGetUserMedia(videoObj, function(stream){
			video.src = window.webkitURL.createObjectURL(stream);
			video.play();
		    }, errBack);
	    }
	    else if(navigator.mozGetUserMedia) { // Firefox-prefixed
		navigator.mozGetUserMedia(videoObj, function(stream){
			video.src = window.URL.createObjectURL(stream);
			video.play();
		    }, errBack);
	    }
	    document.getElementById('$snap_id').addEventListener('click', function() {
		    context.drawImage(video, 0, 0, 640, 480);
		    var input = document.getElementById('$webcam_id');
		    if (input) {
			input.value = canvas.toDataURL('image/png');
		    }
		});
             return false;
";
        $js = 'window.addEvent("domready", function() { ' . $js . '});';
        $webcamNode->appendChild($template->createElement('script',array('type'=>'text/javascript'),$js));
        
    }



    /**
     *get the default extension for this image
     *@returns string
     */
    protected function defaultExtension() {
        return 'jpg';
    }


    /**
     *get the default extension for this image
     *@returns string
     */
    protected function defaultMimeType() {
        return "image/jpeg";
    }


    protected $image_rsrc = null;


    /**
     * Returns an image resource (see PHP GD http://us2.php.net/manual/en/ref.image.php) for the internal data.     This resource can then be manipulated (resized etc).  Once manipulation is done, then {@see setFromReosurce} should be called.
     * @returns mixed.  False on failure, an image resource on success.
     */
    public function getImageResource() {
        if (!$this->createImageResource()) {
            return false;
        }
        return $this->image_rsrc;
    }

    /**
     * Reload the image data from the image reousrce
     * @returns boolean true on success
     */
    public function setFromResource() {
        if (!is_resource($this->image_rsrc)) {
            return false;
        }
        //IMG_GIF | IMG_JPG | IMG_PNG | IMG_WBMP | IMG_XPM. 
        $extension = strtolower($this->getExtension);
        $export =false;
        switch ($extension) {
        case 'jpg':
        case 'jpeg':
            if (! (imagetypes() & IMG_JPG)) {
                break;
            }
            $export = 'imagejpeg';
            break;
        case 'png':
            if (! (imagetypes() & IMG_PNG)) {
                break;
            }
            $export = 'imagepng';
            break;
        case 'wbmp':
            if (! (imagetypes() & IMG_WBMP)) {
                break;
            }
            $export = 'imagewbmp';
            break;
        case 'xpm':
            if (! (imagetypes() & IMG_XPM)) {
                break;
            }
            $export = 'imagexpm';
            break;
        case 'gif':
            if (! (imagetypes() & IMG_GIF)) {
                break;
            }
            $export = 'imagegif';
            break;
        default:
            break;
        } 
        if (!$extension) {
            I2CE::raiseError("Manipulation of $extension is not supported");
            return false;
        }
        $tmp_file = tempnam(sys_get_tmp_dir(),"FF_IMAGE");
        if (! $export($this->image_rsrc,$tmp_file)) {
            I2CE::raiseError("Could not create temp image file from resource for $extension");
            return false;
        }
        $data = file_get_contents($tmp_file);
        unlink($tmp_file);
        $content_length = strlen($data);
        $this->value = rtrim($data,"\0"); //This was for MDB2, need to test if it's necessary with PDO
        $this->null_term = $content_length - strlen($this->value);
        return true;
        
    }

    protected function createImageResource() {
        if (!function_exists('imagecreatefromstring')) {
            return false;
        }
        $this->image_rsrc = imagecreatefromstring($this->value);
        return is_resource($this->image_rsrc);
    }

    public function setFromData($data,$file_name,$mime_type=false, $fmod_time = false) {
        parent::setFromData($data,$file_name,$mime_type,$fmod_time);
        if (is_resource($this->image_rsrc)) {
            imagedestroy($this->image_rsrc);
            $this->createImageResource();
        }
    }
     
    /**
     * Sets the value of this field from the database format.
     * @param mixed $value
     */
    public function setFromDB( $value ) {
        parent::setFromDB($value);
        if (is_resource($this->image_rsrc)) {
            imagedestroy($this->image_rsrc);
            $this->createImageResource();
        }        
    }

    /**
     * Gets the width of the image in pixels
     * @returns mixed.  Int on success, false on failure
     */
    public function getImageWidth() {
        if (!$this->createImageResource()) {
            return false;
        }
        return imagesx($this->image_rsrc);
    }

    /**
     * Gets the height of the image in pixels
     * @returns mixed.  Int on success, false on failure
     */
    public function getImageHeight() {
        if (!$this->createImageResource()) {
            return false;
        }
        return imagesy($this->image_rsrc);
    }
    

    
    /**
     * Return the display value of this form field as a DOM Node.
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @return DOMNode
     */
    public function getDisplayNode( $node, $template ) {
        $text = $this->getDisplayValue();
        $link = $this->getLink();
        if (!$link || strlen( $this->value ) == 0) { //there is no linked URL to this field so we just display the text
            return $template->createTextNode($text);
        }        
        $link_node = $template->createElement('a',array('href'=>$link));
        while($node->hasChildNodes()) {
            $link_node->appendChild($node->firstChild);
        }
        if ($node->hasAttribute('show_text') && (strtolower($node->getAttribute('show_text')) == 'true' || strtolower($node->getAttribute('show_text')) == '!false')) {
            $link_node->appendChild($template->createTextNode($text));
        }

        if (!$node->hasAttribute('show_image') || strtolower($node->getAttribute('show_image')) == 'true' || strtolower($node->getAttribute('show_image')) == '!false') {
            $width = null;
            $height = null;        
            if ($node->hasAttribute('height')) {
                $height = $node->getAttribute('height');
                $node->removeAttribute('height');
            }
            if ($node->hasAttribute('width')) {
                $width = $node->getAttribute('width');
                $node->removeAttribute('width');
            }

            $attrs = array("src"=>$link,'alt'=>$text,'class'=>'field_image');
            if (!empty($width)) {
                $attrs['width'] = $width;
            }
            if (!empty($height)) {
                $attrs['height'] = $height;
            }
            $link_node->appendChild( $template->createElement('img', $attrs));
        }
        return $link_node;
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
