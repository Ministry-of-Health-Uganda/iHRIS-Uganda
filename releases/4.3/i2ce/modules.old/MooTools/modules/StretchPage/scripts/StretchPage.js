var PageStretcher = new Class({
    stretchElement: null,
    footerElement: null,
    
    initialize: function(id,footer_id) {
	this.stretchElement = document.id(id);
	this.footerElement = document.id(footer_id);
	if (!this.stretchElement) {
	    return;
    }

	window.addEvent('resize',  function(e) {
	    this.stretchPage();
	}.bind(this));
	window.addEvent('load',  function(e) {
	    this.stretchPage();
	}.bind(this));
    },

    stretchPage: function() {
	var height;
	if (!this.stretchElement) {
	    return;
	} 
	var lowestNode = 0;
	var sib = this.stretchElement.previousSibling;
	while ( sib ) {
            if ( sib.nodeType == 1 ) {
		lowestNode = Math.max( lowestNode, this.getLowest( sib ) );
            }
            sib = sib.previousSibling;
	}
	var minHeight = 1;
	if ( lowestNode > this.stretchElement.getCoordinates().top ) {
            minHeight = lowestNode - this.stretchElement.getCoordinates().top;
	}
	
	height = window.getHeight() - this.stretchElement.getCoordinates().top;
	if (this.footerElement) {
	    height += (this.footerElement.getCoordinates().top -  this.footerElement.getCoordinates().bottom);
	}
	height  -= 30;
	if (height < minHeight) {
	    height = minHeight;
	}
	this.stretchElement.setStyle( 'height',height);
    },

    getLowest: function( topNode ) {
	if (topNode.style.display == 'none' || topNode.style.position == 'absolute' || topNode.style.position == 'fixed') {
	    return 0;
	}
	var coords;
    if ( topNode.id ) {
        coords = $(topNode).getCoordinates();
    } else {
        return 0;
    }
        var maxBottom = coords.bottom ;
	if (coords.height != topNode.clientHeight) {
	    //this is a scroll element, don't check any chidlren.
	    return maxBottom;
	}
        var child = topNode.firstChild;
        while( child ) {
            if ( child.nodeType == 1 ) {		
                maxBottom = Math.max( maxBottom, this.getLowest( child ) );
	    }
            child = child.nextSibling;	    
        }
        return maxBottom;
    }

});


