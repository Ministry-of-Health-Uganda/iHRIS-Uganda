var builtinFills = {
    'green' : new ol.style.Fill({ color: 'green' }),
    'blue' : new ol.style.Fill({ color: 'blue' }),
    'red' : new ol.style.Fill({ color: 'red' }),
    'green_opacity' : new ol.style.Fill({ color: 'rgba(0, 255, 0, 0.1)' }),
    'blue_opacity' : new ol.style.Fill({ color: 'rgba(0, 0, 255, 0.1)' }),
    'red_opacity' : new ol.style.Fill({ color: 'rgba(255, 0, 0, 0.1)' })
};
var builtinImages = {
    'green' : new ol.style.Circle({ radius: 3, fill: builtinFills['green'] }),
    'blue' : new ol.style.Circle({ radius: 3, fill: builtinFills['blue'] }),
    'red' : new ol.style.Circle({ radius: 3, fill: builtinFills['red'] })
};
var builtinStrokes = {
    'green' : new ol.style.Stroke({ color: 'green', width: 3 }),
    'blue' : new ol.style.Stroke({ color: 'blue', width: 3 }),
    'red' : new ol.style.Stroke({ color: 'red', width: 3 })
};
var builtinStyles = {
    'Point' : {
        'green' : [ new ol.style.Style({ image: builtinImages['green'] }) ],
        'blue' : [ new ol.style.Style({ image: builtinImages['blue'] }) ],
        'red' : [ new ol.style.Style({ image: builtinImages['red'] }) ],
    },
    'Polygon' : {
        'green' : [ new ol.style.Style({ stroke: builtinStrokes['green'], fill: builtinFills['green_opacity'] }) ],
        'blue' : [ new ol.style.Style({ stroke: builtinStrokes['blue'], fill: builtinFills['blue_opacity'] }) ],
        'red' : [ new ol.style.Style({ stroke: builtinStrokes['red'], fill: builtinFills['red_opacity'] }) ]
    }

};
function builtinStyle( feature, resolution ) {
    style = feature.get('style');
    type = feature.getGeometry().getType();
    if ( style ) {
        if ( builtinStyles[type] && builtinStyles[type][style] ) {
            return builtinStyles[type][style];
        }
    } else {
        return builtinStyles[type]['blue'];
    }
}
