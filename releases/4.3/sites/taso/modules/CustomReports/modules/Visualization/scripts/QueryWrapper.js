/**
 * A google.visualization.Query Wrapper. Sends a
 * query and draws the visualization with the returned data or outputs an
 * error message.
 *
 * DISCLAIMER: This is an example code which you can copy and change as
 * required. It is used with the google visualization API which is assumed to
 * be loaded to the page. For more info see:
 * https://developers.google.com/chart/interactive/docs/reference#Query
 */


/**
 * Constructs a new query wrapper with the given query, visualization,
 * visualization options, and error message container. The visualization
 * should support the draw(dataTable, options) method.
 * @constructor
 */
var QueryWrapper = function(query, visualization, visOptions, errorContainer) {

  this.query = query;
  this.visualization = visualization;
  this.options = visOptions || {};
  this.errorContainer = errorContainer;
  if ( this.options.height ) {
      this.heightScale = Math.round( this.options.height / 10 );
  } else {
      this.heightScale = 35;
  }
  if ( this.options.width ) {
      this.widthScale = Math.round( this.options.width / 10 );
  } else {
      this.widthScale = 59;
  }
  this.currentDataTable = null;

  if (!visualization || !('draw' in visualization) ||
      (typeof(visualization['draw']) != 'function')) {
    throw Error('Visualization must have a draw method.');
  }
};


/** Draws the last returned data table, if no data table exists, does nothing.*/
QueryWrapper.prototype.draw = function() {
  if (!this.currentDataTable) {
    return;
  }
  this.visualization.draw(this.currentDataTable, this.options);
};


/**
 * Sends the query and upon its return draws the visualization.
 * If the query is set to refresh then the visualization will be drawn upon
 * each refresh.
 */
QueryWrapper.prototype.sendAndDraw = function() {
  var query = this.query;
  var self = this;
  query.send(function(response) {self.handleResponse(response)});
};

QueryWrapper.prototype.resize = function( height, width, decrease ) {
    if ( !height ) height = this.heightScale;
    if ( !width ) width = this.widthScale;
    if ( decrease ) {
        this.options.width -= width;
        this.options.height -= height;
    } else {
        this.options.width += width;
        this.options.height += height;
    }
    if ( this.options.width < width ) {
        this.options.width = width;
    }
    if ( this.options.height < height ) {
        this.options.height = height;
    }
    this.draw();
}


/** Handles the query response returned by the data source. */
QueryWrapper.prototype.handleResponse = function(response) {
  this.currentDataTable = null;
  if (response.isError()) {
    this.handleErrorResponse(response);
  } else {
    this.currentDataTable = response.getDataTable();
    for( var prop in this.currentDataTable.getTableProperties() ) {
        this.options[prop] = this.currentDataTable.getTableProperty(prop);
    }
    this.draw();
  }
};


/** Handles a query response error returned by the data source. */
QueryWrapper.prototype.handleErrorResponse = function(response) {
  var message = response.getMessage();
  var detailedMessage = response.getDetailedMessage();
  if (this.errorContainer) {
    google.visualization.errors.addError(this.errorContainer,
        message, detailedMessage, {'showInTooltip': false});
  } else {
    throw Error(message + ' ' + detailedMessage);
  }
};


/** Aborts the sending and drawing. */
QueryWrapper.prototype.abort = function() {
  this.query.abort();
};
