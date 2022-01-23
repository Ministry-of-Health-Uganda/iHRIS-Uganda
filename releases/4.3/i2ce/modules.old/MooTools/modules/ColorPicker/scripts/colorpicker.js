/*
Copyright (c) 2007 Kelly Anderson.  All rights reserved.

Developed by: Kelly Anderson
              http://www.sweetvision.com

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to
deal with the Software without restriction, including without limitation the
rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
sell copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
  1. Redistributions of source code must retain the above copyright notice,
     this list of conditions and the following disclaimers.
  2. Redistributions in binary form must reproduce the above copyright
     notice, this list of conditions and the following disclaimers in the
     documentation and/or other materials provided with the distribution.
  3. Neither the names of Kelly Anderson, Kelly Anderson, nor the names 
     of its contributors may be used to endorse or promote products derived
     from this Software without specific prior written permission.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
CONTRIBUTORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
WITH THE SOFTWARE.
*/


//Add some functions to the mootools supplied color object
//that change the color of the current color rather than 
//returning a clone with the new color.
Color.implement({
	changeHue: function(value){
		this.hsb[0] = value;
		rgb = this.hsb.hsbToRgb();
		this[0] = rgb[0];
		this[1] = rgb[1];
		this[2] = rgb[2];
	},
	changeSaturation: function(value){
		this.hsb[1] = value;
		rgb = this.hsb.hsbToRgb();
		this[0] = rgb[0];
		this[1] = rgb[1];
		this[2] = rgb[2];
	},
	changeBrightness: function(value){
		this.hsb[2] = value;
		rgb = this.hsb.hsbToRgb();
		this[0] = rgb[0];
		this[1] = rgb[1];
		this[2] = rgb[2];
	}
});

ColorPicker = new Class({
	initialize: function(objOptions){
		//Check to see if the browser supports the canvas element.
		testcanvas = document.createElement("canvas");
		if(testcanvas.getContext){
			this.supportsCanvas = true;
			myCTX = testcanvas.getContext('2d');	
			if(!myCTX.getImageData && window.opera){
				this.supportsCanvas = true;
			}else if(!myCTX.getImageData){
				this.supportsCanvas = false;
			}					
		}else{
			this.supportsCanvas = false;
		}

		delete testelement;
		
		this.Options = {};
		if(objOptions){
			this.Options = objOptions;
		}

		//if this is a color us it if not try to make a color out of it.
		this.CurColor = new Color("#000000");
		if(this.Options.initialColor){
			if(this.Options.initialColor.isColor){
				this.CurColor = this.Options.initialColor;
			}else{
				this.CurColor = new Color(this.Options.initialColor);
			}
		}
		
		if(this.Options.attachTo){
			if(typeof(this.Options.attachTo) == "string"){
				this.Parent = document.id(this.Options.attachTo);
			}else{
				this.Parent = this.Options.attachTo;
			}
		}else{
			this.Parent = $E('body');
		}

		//These are member variables to hold function pointers to be called with the 
		//buttons in the color dialog.
		this.OKCallback = null;
		this.CancelCallback = null;
		this.HBClickCallback = null;
		this.SVClickCallback = null;
		this.SVPreviewCallback = null;
		//Hack for document positioning in oppera.
		if(!$E('body').style.padding){
			$E('body').style.padding = "5px";
		}
		
		if(this.Options.OKCallback){
			this.OKCallback = this.Options.OKCallback;
		}
		if(this.Options.CancelCallback){
			this.CancelCallback = this.Options.CancelCallback;
		}
		if(this.Options.HBClickCallback){
			this.HBClickCallback = this.Options.HBClickCallback;
		}
		if(this.Options.SVClickCallback){
			this.SVClickCallback = this.Options.SVClickCallback;
		}
		if(this.Options.SVPreviewCallback){
			this.SVPreviewCallback = this.Options.SVPreviewCallback;
		}

		//Create the main container for our color picker.
		this.Container = new Element("div");
		this.Container.className = "cpDialog";
		this.Container.setStyle("border", "solid 1px #000000");
		this.Container.setStyle("background-color", "#aaa");
		this.Container.setStyle("position", "absolute");
		
		if(this.Options.initialState != "Open"){
			this.Container.setStyle("display", "none");
		}
		
		if(this.Options.left){
			this.Container.setStyle("left", this.Options.left);
		}
		if(this.Options.top){
			this.Container.setStyle("top", this.Options.top);
		}
			
		this.Parent.appendChild(this.Container);
		

		if(this.Options.showTitleBar != false){
			//Make the title bar.
			this.TitleBar = new Element("div");
			this.TitleBar.className = "cpTitleBar";
			this.TitleBar.setStyle("border-bottom", "solid 1px #000000");
			this.TitleBar.style.backgroundColor = "#8888ff";
			this.TitleBar.setStyle("height", "20px");
			this.TitleBar.setStyle("margin-bottom", "3px");
			this.TitleBar.style.position = "relative";
			this.TitleBar.innerHTML = "<div nowrap style='position:absolute; left:0px; cursor:default;'>Color Picker</div>";
			this.Close = new Element("div");
			this.Close.style.height = this.TitleBar.style.height;
			this.Close.style.width = "15px";
			this.Close.innerHTML = "X";
			this.Close.style.position = "absolute";
			this.Close.style.right = "0px";
			this.Close.style.cursor = "default";
			this.Close.addEvent("click", this.handleCancel.bind(this));
			this.TitleBar.appendChild(this.Close);
			if(this.Options.isDraggable != false){
				this.Container.makeDraggable({handle: this.TitleBar});
			}
		}

        this.cTable = new Element("table");
        this.cTable.cellSpacing = 0;
        this.cTable.cellPadding = 0;
        this.cTable.border = 0;
        this.cTbody = new Element("tbody");
        this.cThead = new Element("thead");
        this.cTfoot = new Element("tfoot");
        this.cTable.appendChild(this.cThead);
        this.cTable.appendChild(this.cTbody);
        this.cTable.appendChild(this.cTfoot);
        this.cThead.style.height = "0px";
        this.cTfoot.style.height = "0px";
		if(this.Options.showTitleBar != false){
			this.c1tr = new Element("tr");
			this.tdTop = new Element("td");
			this.tdTop.colSpan  = 3;
			this.tdTop.appendChild(this.TitleBar);
			this.c1tr.appendChild(this.tdTop);
			this.cTbody.appendChild(this.c1tr);
		}
        this.c2tr = new Element("tr");
        this.cTbody.appendChild(this.c2tr);
        this.tdleft = new Element("td");
        this.tdleft.vAlign = "top";
        this.tdleft.Align = "left";
        this.tdmiddel = new Element("td");
        this.tdmiddel.vAlign = "top";
        this.tdmiddel.Align = "left";
        this.tdright = new Element("td");
        this.tdright.vAlign = "top";
        this.tdright.setStyle("padding-left", "3px");
        this.tdright.Align = "left";

        this.c2tr.appendChild(this.tdleft);
        this.c2tr.appendChild(this.tdmiddel);
        this.c2tr.appendChild(this.tdright);
        this.Container.appendChild(this.cTable);

		//If the browser supports the canvas element create one instead of a container
		//for child divs
		if(this.Options.showHueBar != false){
			if(this.supportsCanvas){
				//create the canvas Saturation Value Box.
				this.HueBar = new Element("canvas");
				this.HueBar.className = "cpHueBar";
				this.HueBar.setStyle("margin-right", "5px");
				this.HueBar.setStyle("margin-bottom", "5px");
				this.HueBar.setStyle("margin-left", "3px");
				if(this.Options.hueBarWidth){
					this.HueBar.width=this.Options.hueBarWidth;
				}else{
					this.HueBar.width=30;
				}

				if(this.Options.hueBarHeight){
					this.HueBar.height=this.Options.hueBarHeight;
				}else{
					this.HueBar.height=360;
				}
				//Attach the event for clicks to the canvas element
				this.HueBar.addEvent("click", this.setCurrentHue.bind(this));
				this.tdleft.appendChild(this.HueBar);
				//this.Container.appendChild(this.HueBar);
			}else{
				//create the container for the hue bar.
				this.HueBar = new Element("div");
				this.HueBar.className = "cpHueBar";
				this.HueBar.setStyle("position", "relative");
				if(this.Options.hueBarWidth){
					this.HueBar.setStyle("width", this.Options.hueBarWidth);
				}else{
					this.HueBar.setStyle("width", "30");
				}

				if(this.Options.hueBarHeight){
					this.HueBar.setStyle("height", this.Options.hueBarHeight);
				}else{
					this.HueBar.setStyle("height", "360");
				}
					
				//this.HueBar.setStyle("width", "30px");
				this.HueBar.setStyle("margin-right", "5px");
				this.HueBar.setStyle("margin-bottom", "5px");
				this.HueBar.setStyle("margin-left", "3px");
				this.tdleft.appendChild(this.HueBar);
			}
		}
		//If the browser supports the canvas element create one instead of a container
		//for child divs
		if(this.Options.showSVBox != false){
			if(this.supportsCanvas){
				//create the canvas Saturation Value Box.
				this.SVBox = new Element("canvas");
				this.SVBox.setStyle("margin-bottom", "5px");
				this.SVBox.className = "cpSVBox";
				
				if(this.Options.svBoxWidth){
					this.SVBox.width=this.Options.svBoxWidth;
				}else{
					this.SVBox.width=350;
				}
				
				if(this.Options.svBoxHeight){
					this.SVBox.height=this.Options.svBoxHeight;
				}else{
					this.SVBox.height=360;
				}

				//Attach the event for clicks and mouse move to the canvas element
				this.SVBox.addEvent("click", this.setSelectedColor.bind(this));
				this.SVBox.addEvent("mousemove", this.setPreviewColor.bind(this));
				
				this.tdmiddel.appendChild(this.SVBox);

			}else{
				//create the container for the Saturation Value Box.
				this.SVBox = new Element("div");
				this.SVBox.setStyle("margin-bottom", "5px");
				this.SVBox.className = "cpSVBox";
				//this.SVBox.setStyle("width", "350");
				if(this.Options.svBoxWidth){
					this.SVBox.setStyle("width", this.Options.svBoxWidth)
					//this.SVBox.width=this.Options.svBoxWidth;
				}else{
					this.SVBox.setStyle("width", "350")
					//this.SVBox.width=350;
				}
				
				if(this.Options.svBoxHeight){
					this.SVBox.setStyle("height", this.Options.svBoxHeight)
					//this.SVBox.height=this.Options.svBoxHeight;
				}else{
					this.SVBox.setStyle("height", "360")
					//this.SVBox.height=360;			
				}
				this.tdmiddel.appendChild(this.SVBox);
			}
		}
		
		if(this.Options.showColorInfo != false){
			//create the container for the Color Swatches and Text Boxes.
			this.ColorInfo = new Element("div");
			this.ColorInfo.className = "cpColorInfoContainer";
			this.ColorInfo.setStyle("text-align", "center");
			this.ColorInfo.setStyle("margin-left", "3px");
			this.ColorInfo.setStyle("margin-right", "3px");
			this.ColorInfo.setStyle("margin-bottom", "5px");

			tTable = new Element("table");
			tTable.cellSpacing = 0;
			tTable.cellPadding = 0;
			tTable.border = 0;
			tTbody = new Element("tbody");
			tTable.appendChild(tTbody);
			this.ColorInfo.appendChild(tTable);
			
			if(this.Options.showSelectedSwatch != false){
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.innerHTML = "Color:";
				tempTD.colSpan = 2;
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);
				//create the color swatches.
				this.SelectedColor = new Element("div");
				this.SelectedColor.className = "cpSelectedColor";
				this.SelectedColor.setStyle("width", "50px");
				this.SelectedColor.setStyle("height", "50px");
				this.SelectedColor.setStyle("margin-left", "auto");
				this.SelectedColor.setStyle("margin-right", "auto");
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.appendChild(this.SelectedColor);
				tempTD.colSpan = 2;
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);	
			}

			if(this.Options.showPreviewSwatch != false){
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.innerHTML = "Preview:";
				tempTD.colSpan = 2;
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);
				//create the color swatches.
				this.PreviewColor = new Element("div");
				this.PreviewColor.className = "cpPreviewColor";
				this.PreviewColor.setStyle("width", "50px");
				this.PreviewColor.setStyle("height", "50px");
				this.PreviewColor.setStyle("margin-left", "auto");
				this.PreviewColor.setStyle("margin-right", "auto");

				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.appendChild(this.PreviewColor);
				tempTD.colSpan = 2;
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.innerHTML = "&nbsp;";
				tempTD.colSpan = 2;
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);  	
			}

			//Create the hsv and rgb text fields.
			if(this.Options.showHSVFields != false){
				this.hInput = new Element("input");
				this.hInput.className = "cphInput";
				this.hInput.size = "3";
				this.hInput.name = "h";
				this.hInput.type = "text";
				this.hInput.addEvent('keyup', this.setHSL.bind(this));
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.innerHTML = "H:";
				tempRow.appendChild(tempTD);
				tempTD = new Element("td");
				tempTD.appendChild(this.hInput);
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);				

				this.sInput = new Element("input");
				this.sInput.className = "cpsInput";
				this.sInput.size = "3";
				this.sInput.name = "s";
				this.sInput.type = "text";
				this.sInput.addEvent('keyup', this.setHSL.bind(this));
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.innerHTML = "S:";
				tempRow.appendChild(tempTD);
				tempTD = new Element("td");
				tempTD.appendChild(this.sInput);
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);	

				this.vInput = new Element("input");
				this.vInput.className = "cpvInput";
				this.vInput.size = "3";
				this.vInput.name = "v";
				this.vInput.type = "text";
				this.vInput.addEvent('keyup', this.setHSL.bind(this));
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.innerHTML = "V:";
				tempRow.appendChild(tempTD);
				tempTD = new Element("td");
				tempTD.appendChild(this.vInput);
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.innerHTML = "&nbsp;";
				tempTD.colSpan = 2;
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);             				
			}
			
			if(this.Options.showRGBFields != false){
				this.rInput = new Element("input");
				this.rInput.className = "cprInput";
				this.rInput.size = "3";
				this.rInput.name = "r";
				this.rInput.type = "text";
				this.rInput.addEvent('keyup', this.setRGB.bind(this));
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.innerHTML = "R:";
				tempRow.appendChild(tempTD);
				tempTD = new Element("td");
				tempTD.appendChild(this.rInput);
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);

				this.gInput = new Element("input");
				this.gInput.className = "cpgInput";
				this.gInput.size = "3";
				this.gInput.name = "g";
				this.gInput.type = "text";
				this.gInput.addEvent('keyup', this.setRGB.bind(this));
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.innerHTML = "G:";
				tempRow.appendChild(tempTD);
				tempTD = new Element("td");
				tempTD.appendChild(this.gInput);
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);
				
				this.bInput = new Element("input");
				this.bInput.className = "cpbInput";
				this.bInput.size = "3";
				this.bInput.name = "b";
				this.bInput.type = "text";
				this.bInput.addEvent('keyup', this.setRGB.bind(this));
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.innerHTML = "B:";
				tempRow.appendChild(tempTD);
				tempTD = new Element("td");
				tempTD.appendChild(this.bInput);
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.innerHTML = "&nbsp;";
				tempTD.colSpan = 2;
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);            
			}

			if(this.Options.showOKButton != false){
				//create the ok button and set its onclick callback
				this.okInput = new Element("input");
				this.okInput.className = "cpokInput";
				this.okInput.type = "button";
				this.okInput.name = "OK";
				this.okInput.value = "OK";
				this.okInput.addEvent('click', this.handleOK.bind(this));
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.colSpan = 2;
				tempTD.appendChild(this.okInput);
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);
			}
			
			if(this.Options.showCancelButton != false){
				//create the ok button and set its onclick callback
				this.cancelInput = new Element("input");
				this.cancelInput.className = "cpcancelInput";
				this.cancelInput.type = "button";
				this.cancelInput.name = "Cancel";
				this.cancelInput.value = "Cancel";
				this.cancelInput.addEvent('click', this.handleCancel.bind(this));
				tempRow = new Element("tr");
				tempTD = new Element("td");
				tempTD.colSpan = 2;
				tempTD.appendChild(this.cancelInput);
				tempRow.appendChild(tempTD);
				tTbody.appendChild(tempRow);			
			}
			this.tdright.appendChild(this.ColorInfo);
		}
		
		
		if(this.Options.showHueBar != false){
			//Draw the Hue Bar
			this.drawHueBar();
		}
		if(this.Options.showSVBox != false){
			//Draw the SVBox
			this.drawSVBox();
		}
	},
	//Iterates through all 360 hues and creates a 1px by 30px div for each hue.
	drawHueBar: function (){
		if(this.supportsCanvas){
			//get the multiplyer for the hue range based on the height of the hue bar.
			hSteps = 360 / this.HueBar.height;
			hColor = new Color([0,100,100], 'hsb');
			//Get a 2d context to the hue bar canvas element.
			myCTX = this.HueBar.getContext('2d');
			strhHex = "";
			//Iterate over the hight of the hue bar filling 1px tall areas
			//with the proper hue.
			for(hi =  this.HueBar.height; hi > 0; hi--){
				//Get the color for the hew at this iteration.
				hColor.changeHue(hi*hSteps);
				//Get the hex string of the color.
				strhHex = hColor.rgbToHex();
				//Set the context's fillStyle to the color.
				myCTX.fillStyle = strhHex;
				//fill from the left of the hue bar, to the width of the hue bar
				//with the offset hi pixels from the bottom with a height of 1px.
				myCTX.fillRect(0, this.HueBar.height-hi, this.HueBar.width, 1);
			}		
		}else{		
			//Create a new color with hue at 0 and S and V at 100%
			hSteps = 360 / parseInt(this.HueBar.style.height);
			hsvColor = new Color([0,100,100], 'hsb');
			//Create the one Hue element and set it's styles.
			fcoDiv = new Element("div");
			//fcoDiv.setStyle("width","30px");
			fcoDiv.style.width = this.HueBar.style.width;
			fcoDiv.setStyle("height","1px");
			//Loop through the hues
			for(ci = 0; ci < parseInt(this.HueBar.style.height); ci++){
				//User the changeHue to change the existing color object's hue.
				hsvColor.changeHue(ci*hSteps);
				//Clone the Hue element and inject it inside the Hue Bar.
				coDiv = fcoDiv.clone().injectInside(this.HueBar);
				//Set the huse of the new Hue Element
				coDiv.setStyle("background-color", hsvColor);
				//Set the onClick callback function for the new Hue element.
				coDiv.addEvent('click', this.setCurrentHue.bind(this));
			}
		}
	},
	//Draw a SV box that is as tall as the HUE Bar.
	drawSVBox: function(objsvDiv){
		if(this.supportsCanvas){
			//Get the rgb multiplyer for the number of steps over the height of the SVBox
			vSteps = 255 / this.SVBox.height;
			//Get the value multiplyer for the number of steps over the height of the SVBox 
			svSteps = 100 / this.SVBox.height;
			//Create a new color for calculating each rows color ranges.
			svColor = new Color([this.CurColor.hsb[0], 100, 100], "hsb");
			//Get a 2d context to the SVBox canvas element.
			myCTX = this.SVBox.getContext('2d');
			strsvHex = "";
			//Iterate over the hieght of the SVBox.
			for(vi =  this.SVBox.height; vi > 0; vi--){
				//Set the brightness for this row
				svColor.changeBrightness(vi*svSteps);
				//Get the hex string for the current brightness.
				strsvHex = svColor.rgbToHex();
				//Create a new linear Gradient from the canvas context that goes
				//from the left to the right.
				myLinearGrad = myCTX.createLinearGradient(0, 0, this.SVBox.width, 0);
				//Add a color stop to the gradient that is the current
				//bright ness no need to to the HSV conversion here 
				ci = Math.round(vi*vSteps);
				myLinearGrad.addColorStop(0, "rgb("+ci+","+ci+","+ci+")"); 
				//Add a color stop that is based on the current Hue
				myLinearGrad.addColorStop(1, strsvHex); 
				//Set the canvas context's fill style to our current gradient.
				myCTX.fillStyle = myLinearGrad;
				//Fill the row at 1px high.
				myCTX.fillRect(0, this.SVBox.height-vi, this.SVBox.height, 1);
			}		
		}else{		
			//Specify the size of the SV Box svSize is used for both width and height.
			//svSize = 35;
			numSVpx = 30;
			//Set the size of the "pixels" for the SVBox.
			//pixelSize = 10;
			pixelW = Math.floor(parseInt(this.SVBox.style.width) / numSVpx);
			pixelH = Math.floor(parseInt(this.SVBox.style.height) / numSVpx);
			pixelWr = (parseInt(this.SVBox.style.width) / numSVpx % 1) * numSVpx;
			pixelHr = (parseInt(this.SVBox.style.height) / numSVpx % 1) * numSVpx;

			//Get the multiple required to go from 0 to 100 is svSize steps.
			svStep = 100/numSVpx;
			
			//Create the color we will use to set the color of the SV Pixels
			svColor =  new Color([this.CurColor[0],this.CurColor[1],this.CurColor[2]]);
			
			//Create a div that will hold 1 row of pixels.
			// this is done to prevent to reduce the number of 
			// appends into the document.
			rcDiv = new Element("div");
			rcDiv.style.clear = "left";
			//rcDiv.style.width = (svSize*pixelSize)+"px";
			rcDiv.style.width = parseInt(this.SVBox.style.width)+"px";
			//rcDiv.style.height = pixelSize+"px";
			rcDiv.style.height = pixelH+"px";
			
			//Create the one pixel that we will clone for each pixel.
			fcoDiv = new Element("div");
			fcoDiv.setStyle("width", pixelW + "px");
			fcoDiv.setStyle("height", pixelH + "px");
			fcoDiv.setStyle("cssFloat", "left");
			fcoDiv.setStyle("styleFloat", "left");

			//Iterate over each step in the row injecting cloaned divs into it.
			for(ei = 0; ei < numSVpx; ei++){
				fcoDiv.clone().injectInside(rcDiv);
			}
			fcoDiv.clone().injectInside(rcDiv).style.width = pixelWr;

			//Iterate over the height of the SV box setting each rows colors.
			for(vi = numSVpx; vi >= 0; vi -= 1){
				//set the brightness for this row.
				svColor.changeBrightness(vi*svStep);
				//Cloan the row into the SVBox container.
				trcDiv = rcDiv.clone().injectInside(this.SVBox);
				//Get the children of this row.
				arChildren = trcDiv.getChildren();
				if(vi == 0){
					trcDiv.style.height = pixelHr + "px";
				}
				//Iterate the children of this row.
				for(ci = 0; ci < arChildren.length; ci++){
					//Change the saturation for each pixe.
					svColor.changeSaturation(ci*svStep);
					arChildren[ci].style.height = trcDiv.style.height;
					arChildren[ci].setStyle("background-color", svColor);
					//Set the events for each pixels for color choice and preview.
					arChildren[ci].addEvent('click', this.setSelectedColor.bind(this));
					arChildren[ci].addEvent('mouseover', this.setPreviewColor.bind(this));
				}
			}
		}
	},
	//Update the pixels in the SVBox when the hue is clicked.
	UpdateSVBox: function (){
		if(this.Options.showSVBox != false){
			if(this.supportsCanvas){
				vSteps = 255 / this.SVBox.height;
				svSteps = 100 / this.SVBox.height;
				svColor = new Color([this.CurColor.hsb[0], 100, 100], "hsb");
				myCTX = this.SVBox.getContext('2d');
				strsvHex = "";
				for(vi =  this.SVBox.height; vi > 0; vi--){
					svColor.changeBrightness(vi*svSteps);
					strsvHex = svColor.rgbToHex();
					myLinearGrad = myCTX.createLinearGradient(0, 0, this.SVBox.width, 0);
					ci = Math.round(vi*vSteps);
					myLinearGrad.addColorStop(0, "rgb("+ci+","+ci+","+ci+")"); 
					myLinearGrad.addColorStop(1, strsvHex); 
					myCTX.fillStyle = myLinearGrad;
					myCTX.fillRect(0, this.SVBox.height-vi, this.SVBox.height, 1);
				}		
			}else{		
				//Get the child rows in the SVBox
				arSVRows = this.SVBox.getChildren();
				//set the svSize to the number of rows.
				svSize = arSVRows.length;
				//Get the multiple required to go from 0 to 100 is svSize steps.
				svStep = 100/svSize;
				//Create a color for calculating the HSV of each pixel.
				svColor =  new Color([this.CurColor[0],this.CurColor[1],this.CurColor[2]]);
				
				//Loop over all of the rows.
				for(vi = svSize - 1; vi >= 0; vi -= 1){
					//set the brightness for this row.
					svColor.changeBrightness(vi*svStep);
					//Get the children of the row.
					siChildren = arSVRows[svSize - vi - 1].getChildren();
					//Iterate the children of this row.
					for(si = 0; si < siChildren.length; si++){
						//Change the saturation for each pixe.
						svColor.changeSaturation(si*svStep);
						siChildren[si].setStyle("background-color", svColor);		
					}	
				}
			}
		}

	},
	setCurrentHue: function (e){
		//IE uses srcElement instead of target to specify the 
		//element that was clicked.
		if(!e.target){
			e.target = e.srcElement;
		}
		
		if(this.supportsCanvas){
			//Get a 2d context to the SVBox canvas element.
			myCTX = this.HueBar.getContext('2d');
			if(!myCTX.getImageData && window.opera){
				myCTX = this.HueBar.getContext('opera-2dgame');
			}
			//Get the coordinates for our hue bar.
			hBoxCoords = this.HueBar.getCoordinates();
			//subtract the left and top of the hue bar from the event.clentX and y then add the window.scrollX and Y
			// to get the click position in the Hue bar and pass those in to the contexts getImageData function.
			//alert(window.getScrollTop());
			xPos = e.clientX - hBoxCoords.left + window.getScrollLeft();
			yPos = e.clientY - hBoxCoords.top + window.getScrollTop();
			//alert(e.clientX + " - " + hBoxCoords.left + " + " + window.getScrollLeft());
			//alert(myCTX.getPixel(xPos, yPos) + ", for " + xPos + ", " +  yPos);
			if(!myCTX.getImageData){
				myImageData = myCTX.getPixel(xPos, yPos);
				//Create a hue color based of the ImageData returned by the getImageData function.
				CurHueColor = new Color(myImageData);
			}else{
				myImageData = myCTX.getImageData(xPos, yPos, 1, 1);
				//Create a hue color based of the ImageData returned by the getImageData function.
				CurHueColor = new Color([myImageData.data[0], myImageData.data[1], myImageData.data[2]]);
			}
		}else{
			//Create a color object from the background of the target so we can
			//get its Hue.
			CurHueColor = new Color(e.target.getStyle("background-color"));
		}
		//Set the Hue of the current color.
		this.CurColor.changeHue(CurHueColor.hsb[0]);
		//Tell the SVBox to update.
		this.UpdateSVBox();
		//Set the selected color to the current color.
		if(this.Options.showSelectedSwatch != false && this.Options.showColorInfo != false){
			this.SelectedColor.setStyle("background-color", this.CurColor);
		}
		//Update the hsv and rgb text boxes.
		if(this.Options.showHSVFields != false && this.Options.showColorInfo != false){
			this.hInput.value = this.CurColor.hsb[0];
			this.sInput.value = this.CurColor.hsb[1];
			this.vInput.value = this.CurColor.hsb[2];
		}
		if(this.Options.showRGBFields != false && this.Options.showColorInfo != false){
			this.rInput.value = this.CurColor[0];
			this.gInput.value = this.CurColor[1];
			this.bInput.value = this.CurColor[2];	
		}
	},
	setPreviewColor: function (e){
		//IE uses srcElement instead of target to specify the 
		//element that was clicked.
		if(!e.target){
			e.target = e.srcElement;
		}
		if(this.supportsCanvas){
			myCTX = this.SVBox.getContext('2d');
			if(!myCTX.getImageData  && window.opera){
				myCTX = this.SVBox.getContext('opera-2dgame');
			}
						
			SVBoxCoords = this.SVBox.getCoordinates();
			if(!myCTX.getImageData){
				myImageData = myCTX.getPixel(e.clientX - SVBoxCoords.left + window.getScrollLeft(), e.clientY - SVBoxCoords.top + window.getScrollTop());
				//alert(e.clientX - SVBoxCoords.left + window.getScrollLeft());
				nColor = new Color(myImageData);			
			}else{
				myImageData = myCTX.getImageData(e.clientX - SVBoxCoords.left + window.getScrollLeft(), e.clientY - SVBoxCoords.top + window.getScrollTop(), 1, 1);
				nColor = new Color([myImageData.data[0], myImageData.data[1], myImageData.data[2]]);
			}
			//this.PreviewColor.setStyle("background-color", nColor);		
		}else{
			nColor = new Color(e.target.getStyle("background-color"));
			
		}
		if(this.Options.showPreviewSwatch != false && this.Options.showColorInfo != false){
			this.PreviewColor.setStyle("background-color", nColor);
		}
		if(this.SVPreviewCallback){
			this.SVPreviewCallback();
		}

	},

	setSelectedColor: function (e){
		//IE uses srcElement instead of target to specify the 
		//element that was clicked.
		if(!e.target){
			e.target = e.srcElement;
		}
		if(this.supportsCanvas){
			myCTX = this.SVBox.getContext('2d');
			if(!myCTX.getImageData  && window.opera){
				myCTX = this.SVBox.getContext('opera-2dgame');
			}
			
			SVBoxCoords = this.SVBox.getCoordinates();

			if(!myCTX.getImageData){
				myImageData = myCTX.getPixel(e.clientX - SVBoxCoords.left + window.getScrollLeft(), e.clientY - SVBoxCoords.top + window.getScrollTop());
				//alert(e.clientX - SVBoxCoords.left + window.getScrollLeft());
				nColor = new Color(myImageData);			
			}else{
				myImageData = myCTX.getImageData(e.clientX - SVBoxCoords.left + window.getScrollLeft(), e.clientY - SVBoxCoords.top + window.getScrollTop(), 1, 1);
				nColor = new Color([myImageData.data[0], myImageData.data[1], myImageData.data[2]]);
			}

			//myImageData = myCTX.getImageData(e.clientX - SVBoxCoords.left + window.scrollX, e.clientY - SVBoxCoords.top + window.scrollY, 1, 1);
			//nColor = new Color([myImageData.data[0], myImageData.data[1], myImageData.data[2]]);
		}else{
			nColor = new Color(e.target.getStyle("background-color"));
		}
		//nColor = new Color(e.target.getStyle("background-color"));
		this.CurColor = nColor;
		if(this.Options.showSelectedSwatch != false && this.Options.showColorInfo != false){
			this.SelectedColor.setStyle("background-color", nColor);
		}
		
		//Update the hsv and rgb text boxes.
		if(this.Options.showHSVFields != false && this.Options.showColorInfo != false){
			this.hInput.value = nColor.hsb[0];
			this.sInput.value = nColor.hsb[1];
			this.vInput.value = nColor.hsb[2];
		}

		if(this.Options.showRGBFields != false && this.Options.showColorInfo != false){
			this.rInput.value = nColor[0];
			this.gInput.value = nColor[1];
			this.bInput.value = nColor[2];	
		}

		if(this.SVClickCallback){
			this.SVClickCallback();
		}

	},
	setRGB: function (){
		nc = new Color([this.rInput.value, this.gInput.value, this.bInput.value]);
		this.CurColor = nc;
		if(this.Options.showHSVFields != false && this.Options.showColorInfo != false){
			this.hInput.value = nc.hsb[0];
			this.sInput.value = nc.hsb[1];
			this.vInput.value = nc.hsb[2];
		}

		if(this.Options.showSelectedSwatch != false && this.Options.showColorInfo != false){
			this.SelectedColor.setStyle("background-color", nc);
		}
		if(this.Options.showPreviewSwatch != false && this.Options.showColorInfo != false){
			this.PreviewColor.setStyle("background-color", nc);
		}
		this.UpdateSVBox();
	},
	setHSL: function (){
		nc = new Color([this.hInput.value, this.sInput.value, this.vInput.value], 'hsb');
		this.CurColor = nc;
		if(this.Options.showRGBFields != false){
			this.rInput.value = nc[0];
			this.gInput.value = nc[1];
			this.bInput.value = nc[2];	
		}

		if(this.Options.showSelectedSwatch != false && this.Options.showColorInfo != false){
			this.SelectedColor.setStyle("background-color", nc);
		}
		if(this.Options.showPreviewSwatch != false && this.Options.showColorInfo != false){
			this.PreviewColor.setStyle("background-color", nc);
		}
		this.UpdateSVBox();
	},
	//When the ok button is clicked call this.OKCallBack();
	handleOK: function (){
		if(this.OKCallback != null){
			this.OKCallback();
		}
	},
	//When the ok button is clicked call this.CancelCallback();
	handleCancel: function (){
		if(this.CancelCallback != null){
			this.CancelCallback();
		}
	},
	open: function (){
		this.Container.setStyle("display", "block");
	},
	close: function (){
		this.Container.setStyle("display", "none");
	}

});