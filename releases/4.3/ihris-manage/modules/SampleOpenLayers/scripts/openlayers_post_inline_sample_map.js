var displayFeatureInfo = function(pixel) {
    var features = []; 
    map.forEachFeatureAtPixel(pixel, function(feature, layer) {
        features.push(feature);
    }); 
    if (features.length > 0) {
        var facilities = []; 
        var district = ''; 
        var i, ii; 
        for (i = 0, ii = features.length; i < ii; ++i) {
            if ( features[i].get('Level') && features[i].get('Name') ) {
                if ( features[i].get('Level') == 'District' ) district = features[i].get('Name');
                if ( features[i].get('Level') == 'Facility' ) facilities.push(features[i].get('Name'));
            }
        }
        document.getElementById('map_feature_details').innerHTML = 
            "District: " 
            + ( district || '(unknown)' )
            + "<br />\nFacilities: " 
            + ( facilities.join(', ') || '(unknown)' );
    } else {
        document.getElementById('map_feature_details').innerHTML = '&nbsp;';
    }
};
map.on('click', function(evt) {
    displayFeatureInfo(evt.pixel);
});
