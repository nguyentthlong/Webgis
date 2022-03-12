<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>OpenStreetMap &amp; OpenLayers - Marker Example</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css" />
    <script src="https://openlayers.org/en/v4.6.5/build/ol.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        .map,
        .righ-panel {
            height: 98vh;
            width: 80vw;
            float: left;
        }

        .map {
            border: 1px solid #000;
        }

        .ol-popup {
            position: absolute;
            background-color: white;
            -webkit-filter: drop-shadow(0 1px 4px rgba(0, 0, 0, 0.2));
            filter: drop-shadow(0 1px 4px rgba(0, 0, 0, 0.2));
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #cccccc;
            bottom: 12px;
            left: -50px;
            min-width: 180px;
        }

        .ol-popup:after,
        .ol-popup:before {
            top: 100%;
            border: solid transparent;
            content: " ";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
        }

        .ol-popup:after {
            border-top-color: white;
            border-width: 10px;
            left: 48px;
            margin-left: -10px;
        }

        .ol-popup:before {
            border-top-color: #cccccc;
            border-width: 11px;
            left: 48px;
            margin-left: -11px;
        }

        .ol-popup-closer {
            text-decoration: none;
            position: absolute;
            top: 2px;
            right: 8px;
        }

        .ol-popup-closer:after {
            content: "✖";
        }
    </style>
</head>

<body onload="initialize_map();">

    <table>

        <tr>

            <td>
                <div id="map" class="map"></div>
                <div id="map" style="width: 50vw; height: 50vh;"></div> 
                <div id="popup" class="ol-popup">
                    <a href="#" id="popup-closer" class="ol-popup-closer"></a>
                    <div id="popup-content"></div>
                </div>
               
            </td>
            <td>     
                <input type="textinput" id="city" placeholder="Nhập tên thành phố cần tìm" >
                <button id="btnSearch" type="submit"><i class="fa fa-search"></i></button>
                <br />
                <input onclick="oncheck_vn()" type="checkbox" id="vn" name="layer" value="vn"> Việt Nam<br />
				<input onclick="oncheck_river();" type="checkbox" id="river" name="layer" value="river"> Sông <br />
				<input onclick="oncheck_seaport();" type="checkbox" id="seaport" name="layer" value="seaport"> Cảng Biển <br />				
				<input onclick="oncheck_airport()" type="checkbox" id="airport" name="layer" value="airport"> Cảng Hàng Không<br />
				<input onclick="oncheck_rails()" type="checkbox" id="rails" name="layer" value="rails"> Đường Sắt<br />
				<input onclick="oncheck_road()" type="checkbox" id="road" name="layer" value="road"> Đường Bộ<br />
            </td>
        </tr>
    </table>
    <?php include 'CMR_pgsqlAPI.php' ?>

    <script>
        var format = 'image/png';
        var map;
        var minX = 102.107963562012;
        var minY = 8.30629825592041;
        var maxX = 109.505798339844;
        var maxY = 23.4677505493164;
        var cenX = (minX + maxX) / 2;
        var cenY = (minY + maxY) / 2;
        var mapLat = cenY;
        var mapLng = cenX;
        var mapDefaultZoom = 5;
        var layerCMR_adm1;
        var layer_river;
        var layer_seaport;
		var layer_road;
		var layer_rails;
		var layer_airport;
        var vectorLayer;
        var styleFunction;
        var styles;
        var container = document.getElementById('popup');
        var content = document.getElementById('popup-content');
        var closer = document.getElementById('popup-closer');
        var city = document.getElementById("city");
        var chkVN = document.getElementById("vn");
        var chkSeaport = document.getElementById("seaport");
        var chkRiver = document.getElementById("river");
		var chkAirport = document.getElementById("airport");
		var chkRails = document.getElementById("rails");
        var value ;
        /*
         Create an overlay to anchor the popup to the map.
         */

         var overlay = new ol.Overlay({
            element: container,
            autoPan: true,
            autoPanAnimation: {
                duration: 250
            }
        });
        closer.onclick = function() {
            overlay.setPosition(undefined);
            closer.blur();
            return false;
        };
        function handleOnCheck(id, layer) {
            if (document.getElementById(id).checked) {
                value = document.getElementById(id).value;
                // map.setLayerGroup(new ol.layer.Group())
                map.addLayer(layer)
                vectorLayer = new ol.layer.Vector({});
                map.addLayer(vectorLayer);
            } else {
                map.removeLayer(layer);
                map.removeLayer(vectorLayer);
            }
        }
        function myFunction() {
            var popup = document.getElementById("popup");
            popup.classList.toggle("show");
        }
		
        function oncheck_airport() {
            handleOnCheck('airport', layer_airport);
        }

        function oncheck_rails() {
            handleOnCheck('rails', layer_rails);
        }
        function oncheck_road() {
            handleOnCheck('road', layer_road);
        }		
        function oncheck_seaport() {
            handleOnCheck('seaport', layer_seaport);
        }
        function oncheck_river() {
            handleOnCheck('river', layer_river);
        }
        function oncheck_vn() {
            handleOnCheck('vn', layerCMR_adm1);
        }

        function initialize_map() {
            layerBG = new ol.layer.Tile({
                source: new ol.source.OSM({})
            });

            // Tạo Layer
            layerCMR_adm1 = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/example/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.1',
                        STYLES: '',
                        LAYERS: 'example:gadm36_vnm_1',
                    }
                })

            });

            layer_river = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/example/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.1',
                        STYLES: '',
                        LAYERS: 'example:gis_osm_waterways_free_1',
                    }
                })
            });

            layer_seaport = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/example/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.1',
                        STYLES: '',
                        LAYERS: 'example:cang',
                    }
                })
            });
			
            layer_airport = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/example/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.1',
                        STYLES: '',
                        LAYERS: 'example:sanbay',
                    }
                })
            });

			//rails
			layer_rails = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/example/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.1',
                        STYLES: '',
                        LAYERS: 'example:gis_osm_railways_free_1',
                    }
                })
            });
			
            layer_road = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/example/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.1',
                        STYLES: '',
                        LAYERS: 'example:gis_osm_roads_free_1',
                    }
                })
            });			

            var viewMap = new ol.View({
                center: ol.proj.fromLonLat([mapLng, mapLat]),
                zoom: mapDefaultZoom
            });

            map = new ol.Map({
                target: "map",
                layers: [layerBG],
                view: viewMap,
                overlays: [overlay], 
            });

            var styles = {
                'Point': new ol.style.Style({
                    stroke: new ol.style.Stroke({
                        color: 'yellow',
                        width: 4
                    })
                }),

                'MultiLineString': new ol.style.Style({

                    stroke: new ol.style.Stroke({
                        color: 'red',
                        width: 4
                    })
                }),

                'Polygon': new ol.style.Style({
                    stroke: new ol.style.Stroke({
                        color: 'red',
                        width: 4
                    })
                }),

                'MultiPolygon': new ol.style.Style({
                    fill: new ol.style.Fill({
                        color: 'pink'
                    }),
                    stroke: new ol.style.Stroke({
                        color: 'green',
                        width: 4
                    })
                })
            };

            styleFunction = function(feature) {
                return styles[feature.getGeometry().getType()];
            };

            vectorLayer = new ol.layer.Vector({
                style: styleFunction
            });
            map.addLayer(vectorLayer);
		
			var button = document.getElementById("btnSearch").addEventListener("click",
                () => {
                    vectorLayer.setStyle(styleFunction);
                    city.value.length ?
                        $.ajax({
                            type: "POST",
                            url: "CMR_pgsqlAPI.php",
                            data: {
                                name: city.value
                            },
                            success: function(result, status, erro) {

                                if (result == 'null')
                                    alert("không tìm thấy đối tượng");
                                else
                                    highLightObj(result);
                            },
                            error: function(req, status, error) {
                                alert(req + " " + status + " " + error);
                            }
                        }) : alert("Nhập dữ liệu tìm kiếm")
                });
				
            function createJsonObj(result) {
                var geojsonObject = '{' +
                    '"type": "FeatureCollection",' +
                    '"crs": {' +
                    '"type": "name",' +
                    '"properties": {' +
                    '"name": "EPSG:4326"' +
                    '}' +
                    '},' +
                    '"features": [{' +
                    '"type": "Feature",' +
                    '"geometry": ' + result +
                    '}]' +
                    '}';
                return geojsonObject;
            }
            function highLightGeoJsonObj(paObjJson) {
                var vectorSource = new ol.source.Vector({
                    features: (new ol.format.GeoJSON()).readFeatures(paObjJson, {
                        dataProjection: 'EPSG:4326',
                        featureProjection: 'EPSG:3857'
                    })
                });
                vectorLayer.setSource(vectorSource);
            }

            function highLightObj(result) {
                var strObjJson = createJsonObj(result);
                var objJson = JSON.parse(strObjJson);
                highLightGeoJsonObj(objJson);
            }

            function displayObjInfo(result, coordinate) {
                $("#popup-content").html(result);
                overlay.setPosition(coordinate);

            }

            map.on('singleclick', function(evt) {
                var myPoint = 'POINT(12,5)';
                var lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857','EPSG:4326');
                var lon = lonlat[0];
                var lat = lonlat[1];
                var myPoint = 'POINT(' + lon + ' ' + lat + ')';
                
                if (value == 'vn') {
                    vectorLayer.setStyle(styleFunction);

                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoCMRToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getGeoCMRToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            highLightObj(result);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
                if (value == "river") {
                    //river
                    vectorLayer.setStyle(styleFunction);
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoRivertoAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getRiverToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            highLightObj(result);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
				
				//cảng biển
                if (value == "seaport") {
                    vectorLayer.setStyle(stylePoint);
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoSeaPortToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getGeoEagleToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            highLightObj(result);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
				
				//airport
				if (value == "sanbay") {
                    vectorLayer.setStyle(stylePoint);
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoAirportToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getAirportToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            highLightObj(result);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
				
				//rails
				if (value == "rails") {
                    vectorLayer.setStyle(stylePoint);
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoRailsToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getRailsToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            highLightObj(result);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
				
				//road - đường bộ
				if (value == "road") {
                    vectorLayer.setStyle(stylePoint);
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoRoadToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getRoadToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            highLightObj(result);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }				
				
            });
        };
    </script>
</body>

</html>