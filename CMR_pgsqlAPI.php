<?php
$paPDO = initDB();
$paSRID = '0';

if (isset($_POST['functionname'])) {
    $paPoint = $_POST['paPoint'];
    $functionname = $_POST['functionname'];
    $aResult = "null";
    if ($functionname == 'getGeoCMRToAjax')
        $aResult = getGeoCMRToAjax($paPDO, $paSRID, $paPoint);
    else if ($functionname == 'getInfoCMRToAjax')
        $aResult = getInfoCMRToAjax($paPDO, $paSRID, $paPoint);
	//inf sông
    else if ($functionname == 'getInfoRivertoAjax')
        $aResult = getInfoRivertoAjax($paPDO, $paSRID, $paPoint);
	//infor_sanbay
    else if ($functionname == 'getInfoAirporttoAjax')
        $aResult = getInfoAirporttoAjax($paPDO, $paSRID, $paPoint);
	//info_cangbien
    else if ($functionname == 'getInfoSeaPortToAjax')
        $aResult = getInfoSeaPortToAjax($paPDO, $paSRID, $paPoint);
	
	//info_road
    else if ($functionname == 'getInfoRoadToAjax')
        $aResult = getInfoRoadToAjax($paPDO, $paSRID, $paPoint);
	//highlight_road
    else if ($functionname == 'getRoadToAjax')
        $aResult = getRoadToAjax($paPDO, $paSRID, $paPoint);
	
	//info_rails
    else if ($functionname == 'getInfoRailsToAjax')
        $aResult = getInfoRailsToAjax($paPDO, $paSRID, $paPoint);
	//highlight_rails
    else if ($functionname == 'getRailsToAjax')
        $aResult = getRailsToAjax($paPDO, $paSRID, $paPoint);
	//highlight_cangbien
    else if ($functionname == 'getGeoEagleToAjax')
        $aResult = getGeoEagleToAjax($paPDO, $paSRID, $paPoint);
	//highlight_song
    else if ($functionname == 'getRiverToAjax')
        $aResult = getRiverToAjax($paPDO, $paSRID, $paPoint);
	//highlight_sanbay
    else if ($functionname == 'getAirportToAjax')
        $aResult = getAirportToAjax($paPDO, $paSRID, $paPoint);	

    echo $aResult;
    closeDB($paPDO);
}

if (isset($_POST['name'])) {
    $name = $_POST['name'];
    $aResult = seacherCity($paPDO, $paSRID, $name);
    echo $aResult;
}

function initDB()
{

    // Kết nối CSDL
    $paPDO = new PDO('pgsql:host=localhost;dbname=TestCSDL;port=5432', 'postgres', 'admin');
	closeDB($paPDO);
    return $paPDO;
}
function query($paPDO, $paSQLStr)
{
    try
    {
        // Khai báo exception
        $paPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Sử đụng Prepare 
        $stmt = $paPDO->prepare($paSQLStr);
        // Thực thi câu truy vấn
        $stmt->execute();
        
        // Khai báo fetch kiểu mảng kết hợp
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        // Lấy danh sách kết quả
        $paResult = $stmt->fetchAll();   
        return $paResult;                 
    }
    catch(PDOException $e) {
        echo "Thất bại, Lỗi: " . $e->getMessage();
        return null;
    }       
}
function closeDB($paPDO)
{
    // Ngắt kết nối
    $paPDO = null;
}

// Hightlight VN
function getGeoCMRToAjax($paPDO, $paSRID, $paPoint)
{
    
    $paPoint = str_replace(',', ' ', $paPoint);
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm36_vnm_1\" 
    where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            return $item['geo'];
        }
    } else
        return "null";
}

// Hightlight Cảng biển
function getGeoEagleToAjax($paPDO, $paSRID, $paPoint)
{
    
    $paPoint = str_replace(',', ' ', $paPoint);   
    $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
    $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom)))
     from cang";
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from cang where " 
    . $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            return $item['geo'];
        }
    } else
        return "null";
}

// Hightlight Sông
function getRiverToAjax($paPDO, $paSRID, $paPoint)
{
   
    $paPoint = str_replace(',', ' ', $paPoint);    
    $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
    $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom)))
     from gis_osm_waterways_free_1";
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from gis_osm_waterways_free_1 where " 
    . $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            return $item['geo'];
        }
    } else
        return "null";
}

// Hightlight đường sắt
function getRailsToAjax($paPDO, $paSRID, $paPoint)
{
   
    $paPoint = str_replace(',', ' ', $paPoint);
    
    $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
    $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom)))
     from gis_osm_railways_free_1";
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from gis_osm_railways_free_1 where " 
    . $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            return $item['geo'];
        }
    } else
        return "null";
}

// Hightlight đường bộ
function getRoadToAjax($paPDO, $paSRID, $paPoint)
{
   
    $paPoint = str_replace(',', ' ', $paPoint);
    
    $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
    $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom)))
     from gis_osm_roads_free_1";
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from gis_osm_roads_free_1 where " 
    . $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            return $item['geo'];
        }
    } else
        return "null";
}


// Hightlight sân bay
function getAirportToAjax($paPDO, $paSRID, $paPoint)
{
    
    $paPoint = str_replace(',', ' ', $paPoint);   
    $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
    $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom)))
     from sanbay";
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from sanbay where " 
    . $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            return $item['geom'];
        }
    } else
        return "null";
}

// Truy vấn thông tin VN
function getInfoCMRToAjax($paPDO, $paSRID, $paPoint)
{
   
    $paPoint = str_replace(',', ' ', $paPoint);
    $mySQLStr = "SELECT gid, name_1, ST_Area(geom) as dt, ST_Perimeter(geom) as cv 
    from \"gadm36_vnm_1\" where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)";
    
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        $resFin = '<table>';
        // Lặp kết quả
        foreach ($result as $item) {
            $resFin = $resFin . '<tr><td>GID: ' . $item['gid'] . '</td></tr>';
            $resFin = $resFin . '<tr><td>Tên Tỉnh: ' . $item['name_1'] . '</td></tr>';
            $resFin = $resFin . '<tr><td>Diện Tích: ' . $item['dt'] . ' km2 ' .'</td></tr>';
            $resFin = $resFin . '<tr><td>Chu vi: ' . $item['cv'] . ' km '.'</td></tr>';
            break;
        }
        $resFin = $resFin . '</table>';
        return $resFin;
    } else
        return "null";
}

//Truy vấn thông tin sông
function getInfoRivertoAjax($paPDO, $paSRID, $paPoint)
{
    $paPoint = str_replace(',', ' ', $paPoint);
    $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
    $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom)))
     from gis_osm_waterways_free_1";
    $mySQLStr = "SELECT *  from gis_osm_waterways_free_1 where " . $strDistance . "
     = (" . $strMinDistance . ") and " . $strDistance . " < 0.1";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        $resFin = '<table>';
        // Lặp kết quả
        foreach ($result as $item) {
            $resFin = $resFin . '<tr><td>Tên sông: ' . $item['name'] . '</td></tr>';
            break;
        }
        $resFin = $resFin . '</table>';
        return $resFin;
    } else
        return "null";
}

//Truy vấn thông tin đường sắt
function getInfoRailstoAjax($paPDO, $paSRID, $paPoint)
{
    $paPoint = str_replace(',', ' ', $paPoint);
    $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
    $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom)))
     from gis_osm_railways_free_1";
    $mySQLStr = "SELECT * from gis_osm_railways_free_1 where " . $strDistance . "
     = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        $resFin = '<table>';
        // Lặp kết quả
        foreach ($result as $item) {
            $resFin = $resFin . '<tr><td> Tên đường sắt: ' . $item['name'] . '</td></tr>';
            break;
        }
        $resFin = $resFin . '</table>';
        return $resFin;
    } else
        return "null";
}

function getInfoRoadtoAjax($paPDO, $paSRID, $paPoint)
{
    $paPoint = str_replace(',', ' ', $paPoint);
    $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
    $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom)))
     from gis_osm_roads_free_1";
    $mySQLStr = "SELECT *  from gis_osm_roads_free_1 where " . $strDistance . "
     = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        $resFin = '<table>';
        // Lặp kết quả
        foreach ($result as $item) {
            $resFin = $resFin . '<tr><td> Tên đường bộ: ' . $item['name'] . '</td></tr>';
            break;
        }
        $resFin = $resFin . '</table>';
        return $resFin;
    } else
        return "null";
}

//Truy vấn thông tin sân bay
function getInfoAirporttoAjax($paPDO, $paSRID, $paPoint)
{
    $paPoint = str_replace(',', ' ', $paPoint);
    $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
    $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom)))
     from sanbay";
	 
	 //mySQLStr = "SELECT ten, loai from \"sanbay\" where ". $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
    $mySQLStr = "SELECT * from sanbay where " . $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        $resFin = '<table>';
        // Lặp kết quả
        foreach ($result as $item) {
            $resFin = $resFin . '<tr><td>Tên sân bay: ' . $item['ten'] . '</td></tr>';
            break;
        }
        $resFin = $resFin . '</table>';
        return $resFin;
    } else
        return "null";
}

// Truy vấn thông tin cảng
function getInfoSeaPortToAjax($paPDO, $paSRID, $paPoint)
{
    $paPoint = str_replace(',', ' ', $paPoint);
    $strDistance = "ST_Distance('" . $paPoint . "',ST_AsText(geom))";
    $strMinDistance = "SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom)))
     from cang";
	 
	//$mySQLStr = "SELECT ten_cang, loai from \"cang\" where ". $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
	
    $mySQLStr = "SELECT * from cang where " . $strDistance . " = (" . $strMinDistance . ") and " . $strDistance . " < 0.05";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        $resFin = '<table>';
        // Lặp kết quả
        foreach ($result as $item) {
        $resFin = $resFin . '<tr><td>Tên cảng: ' . $item['ten_cang'] . '</td></tr>';
        $resFin = $resFin . '<tr><td>Loại: ' . $item['loai'] . '</td></tr>';
            break;
        }
        $resFin = $resFin . '</table>';
        return $resFin;
    } else
        return "null";
}

//Tìm kiếm
function seacherCity($paPDO, $paSRID, $name)
{
    
    $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from gadm36_vnm_1
     where name_1 like '$name'";
    $result = query($paPDO, $mySQLStr);

    if ($result != null) {
        // Lặp kết quả
        foreach ($result as $item) {
            return $item['geo'];
        }
    } else
        return "null";
}
