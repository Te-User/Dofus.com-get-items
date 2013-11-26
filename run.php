<meta charset="utf-8" />
<title>DOFUS Official Items</title>
<style>
    #hoster
    {
        display: inline-block;
        float: center;
        text-align: center;
    }
    table 
    {
        font-family: Tahoma;
        font-size: 12px;
        margin-top: 15px;
        width: 45%;
        display: inline-block;
        border-collapse:collapse;   
        background-color: #C9BF9C;
        border: 1px #000 solid;
    }
    td
    {
        position: relative;
        padding: 5px 5px 5px 5px;
    }
    #statsViewer, #statsViewerEncoded
    {
        height: 100px;
        overflow: auto;
    }
    #statsViewer
    {
        width: 300px;
        padding-left: 10px;
    }
    #titleI
    {
        background-color: #534A3B;
        color: white;
        font-weight: bold;
        font-size: 15px;
        text-transform: uppercase;
    }
    #imgView
    {
        background-color: #C6C29D;
    }
    textarea 
    {
        background-color: #C6C29D;
        font-family: Tahoma;
        border: 0;
    }
    a
    {
        text-decoration: none;
        margin-top: 15px;
        font-family: Verdana;
        font-size: 12px;
        color: orange;
        font-weight:bold;
    }
    #gen_line input
    {
        width: 100%;
    }
</style>
<?php
    /*
        Classes.
    */
    class createSWF
    {
        public $Line = "undefined";
        public function __construct($Obj) 
        {
            $isCaC = false;
            if($isCaC) 
            {
                $makeLine = 'I.u['.(int)$Obj->id.'] = {p: 0, c: "", e: [5, 4, 1, 1, 50, 30, false, false], an: 14, w: 20, fm: true, wd: true, l: 2, g: 7, ep: 1, d: "'.$this->adapt($Obj->Description).'", t: 6, n: "'.$this->adapt($Obj->Nom).'"};';
            } 
            else
            {
                $makeLine = 'I.u['.(int)$Obj->id.'] = {p: 0, c: "", w: 20, fm: true, wd: true, l: '.(int)$Obj->Level.', g: '.(int)$Obj->id.', ep: 1, d: "'.$this->adapt($Obj->Description).'", t: '.(int)$Obj->Type.', n: "'.$this->adapt($Obj->Nom).'"};';
            }
            $this->Line = $makeLine;
        }
        private function adapt($value)
        {
            return str_replace("'", "\'", $value);
        }
    }
    class createQuery
    {
        public $Query = "none";
        public function __construct($Obj, $Stats) 
        {
            $Table_Item = 'item_template';
            $Columns = Array
            (
                "id",               // ID de l'item.
                "name",             // Nom de l'objet.
                "type",             // Type de l'objet.
                "level",            // Level de l'objet.
                "statsTemplate"     // Stats de l'objet.
            );
            $Obj->Level = explode(" ", $Obj->Level);
            $makeQuery = 'INSERT INTO `'.$Table_Item.'` (`'.$Columns[0].'`, `'.$Columns[1].'`, `'.$Columns[2].'`, `'.$Columns[3].'`, `'.$Columns[4].'`) VALUES (`'.$Obj->id.'`, `'.$Obj->Nom.'`, `'.$Obj->Type.'`, `'.$Obj->Level[1].'`, `'.$Stats.'`) ';
            $this->Query = $makeQuery;
        }
    }
    class Item 
    {
        public $id;
        public $Nom;
        public $Type;
        public $Level;
        public $Description = "";
        public $Stats;
        public $StatsTab = Array();
        public $StatsTabR = Array();
        public $StatsTabEncoded = Array();
         
        public function __construct($Infos) 
        {
            $this->id        = $Infos[0];
            $this->Nom       = $Infos[1];
            $this->Level     = $Infos[2];
            $this->Stats     = $Infos[3];
            $this->Type      = $Infos[4];
            $this->encodeStats($this->parseStats($this->Stats));
        }
         
        private function parseStats ($statsHTML) 
        {
            $decStats = explode('<li class="', $statsHTML);
            for($i = 1; $i < count($decStats); $i++)
            {
                $decLine = explode("</li>", $decStats[$i]);
                $decLine2 = explode('">', $decLine[0]);
                array_push($this->StatsTab, $decLine2[1]);
            }               
        }
         
        private function encodeStats () 
        {
            foreach ($this->StatsTab as $Value) 
            {
                $decInfos = explode(" ", $Value);
                $ItemTypeName = $decInfos[count($decInfos) - 1];
                 
                /*
                    Si le jet est un %.
                */
                if(substr_count($Value, "%") != 0) {
                    $ItemTypeName = $decInfos[count($decInfos) - 3] . " " . $decInfos[count($decInfos) - 2] . " " . $decInfos[count($decInfos) - 1];
                } 
                else if(substr_count($Value, " ") >= 4) 
                {
                 
                    /*
                        Si le jet comporte un mix et un maxi.
                    */
                    if(substr_count($Value, "à") != 0) 
                    {
                        $ItemTypeName = $decInfos[count($decInfos) - 2] . " " . $decInfos[count($decInfos) - 1];
                    } else
                    {
                        $ItemTypeName = $decInfos[count($decInfos) - 2] . " " . $decInfos[count($decInfos) - 1];
                    }
                }
                 
                /*
                    Si le jet est négatif.
                */
                if(substr($decInfos[0], 0, 1) == "-") {
                    $ItemTypeName = "-" . $ItemTypeName;
                }
                 
                /*
                    Si le code du jet est disponible.
                */
                if($this->getElement($ItemTypeName) != null) {
                    $item_info = Array
                    (
                        $this->getElement($ItemTypeName),
                        $decInfos[0],
                        0
                    );
                     
                    /*
                        Si le jet n'est pas fixe.
                    */
                    if(substr_count($Value, "à") != 0) 
                    {
                        $item_info[2] = $decInfos[2];
                    } 
                    else
                    {
                        $item_info[2] = 0;
                    }
                     
                    /*
                        On encode le jet.
                    */
                    $num = 1;
                    $number = (int)$item_info[2]; // max
                    $num3 = (int)$item_info[1]; // min
                    $str2 = dechex($num3);
                    $str = dechex($number);
                    if($number == (int)"0L")
                    {
                        $num = 0;
                    } 
                    else
                    {
                        $number -= $num3 - (int)"1L";
                        $num3 -= (int)"1L";
                    }
                    $encoded_jet = $item_info[0] . "#".$str2."#" . $str . "#0#".(string)$num."d".(string)$number."+" . (string)$num3;
                    array_push($this->StatsTabEncoded, $encoded_jet);
                }
            }
        }
         
        public function getElement ($bText) 
        {
            $list = Array
            (
                "Vitalité" => "7d",
                "Sagesse" => "7c",
                "Force" => "76",
                "Intelligence" => "7e",
                "Agilité" => "77",
                "Chance" => "7b",
                "PA" => "6f",
                "PM" => "80",
                "PO" => "75",
                "Initiative" => "ae",
                "Dommages" => "70",
                "Puissance" => "8a",
                "Coups Critiques" => "73",
                "Prospection" => "b0",
                "Soins" => "b2",
                "Invocations" => "b6",
                "% Résistance Neutre" => "d6",
                "% Résistance Terre" => "d2",
                "% Résistance Feu" => "d5",
                "% Résistance Eau" => "d3",
                "% Résistance Air" => "d4",
                "Résistance Neutre" => "f4",
                "Résistance Terre" => "f0",
                "Résistance Feu" => "f3",
                "Résistance Eau" => "f1",
                "Résistance Air" => "f2",
                "DegatNeutre" => "64",
                "DegatTerre" => "61",
                "DegatFeu" => "63",
                "DegatEau" => "60",
                "DegatAir" => "62",
                "VolNeutre" => "5f",
                "VolTerre" => "5c",
                "VolFeu" => "5e",
                "VolEau" => "5b",
                "VolAir" => "5d"
            );
            if(isset($list[$bText])) 
            {
                return $list[$bText];
            }
            else
            {
                return null;
            }   
        }
    }
    class getSource 
    {   
        public $content;
        public $listItems = Array();
        public $typeSearch;
         
        public function __construct($source, $type)
        {
            $this->content = file_get_contents($source);
            $this->typeSearch = $type;
        }
         
        public function Parser ($src) 
        {
            $byTitle = explode("title_element", $src);
            $newList = Array();
            for ($i = 0; $i<count($byTitle); $i++) 
            {
                $getTitle = explode(');">', $byTitle[$i]);
                $getTitle2 = explode('</h2>', $getTitle[1]);
                if(strlen($getTitle2[0]) <= 50) {
                    $getID = explode('staticns.ankama.com/dofus/www//game/items/src/', $byTitle[$i]);
                    $getID2 = explode('.swf', $getID[1]);
                    $getLevel = explode('level_element">', $byTitle[$i]);
                    $getLevel2 = explode('</span>', $getLevel[1]);
                    $getStats = explode('</span>Effets</span>', $byTitle[$i]);
                    $getStats2 = explode('<div class="element_carac_right">', $getLevel[1]);
                    $getStats3 = explode('<ul>', $getStats2[0]);
                    $getStats4 = explode('</ul>', $getStats3[1]);
                    array_push($this->listItems, new Item(array($getID2[0], $getTitle2[0], $getLevel2[0], $getStats4[0], $this->typeSearch)));
                }
            }
        }
    }
    class itemsType 
    {
        public $textType;
        public $idType;
         
        public function __construct($type) 
        {
            $this->idType    = $type;
            $this->textType = $this->getId($type);
        }
                 
        private function getId ($type) 
        {
            switch($type)
            {
                default:
                    return "1-amulette";
                    break;
                case 1:
                    return "1-amulette";
                    break;
                case 9:
                    return "9-anneau";
                    break;
                case 10:
                    return "10-ceinture";
                    break;
                case 11:
                    return "11-botte";
                    break;
                case 82:
                    return "82-bouclier";
                    break;
                case 16:
                    return "16-chapeau";
                    break;
                case 17: 
                    return "17-cape";
                    break;
                case 81:
                    return "81-sac-dos";
                    break;  
            }
        }
    }
    /*
        Informations.
    */
     
    $links_ankama = array
    (
        "http://www.dofus.com/fr/mmorpg-jeux/objets/2-objets/",
        "http://www.dofus.com/fr/mmorpg-jeux/objets/1-armes/"
    );
    /*
        Éxecution.
    */
    if(isset($_GET['t'])) 
    {
        $typeWant = intval($_GET['t']);
    } 
    else
    {
        $typeWant = 1;
    }
    if(isset($_GET['p'])) 
    {
        $pageWant = intval($_GET['p']);
    } 
    else
    {
        $pageWant = 1;
    }
    $listType = new itemsType($typeWant);
    $source = new getSource($links_ankama[0] . $listType->textType . "?pa=" . $pageWant, $listType->idType);
    echo $source->Parser($source->content);
    echo '<div id="hoster">';
    foreach ($source->listItems as $obj) {
        echo ('
         
            <table>
                <thead>
                    <tr>
                        <td id="titleI" colspan="2">'. $obj->Nom .  ' (' .$obj->id.')</td> 
                        <td id="titleI" align="right"">' . $obj->Level .'</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td id="imgView">
                            <img src="http://images1.dofusbook.net/items/' . $obj->id .'_0.png" height="100" width="100" />
                 
                        </td>
                        <td>
                            <textarea id="statsViewerEncoded">');
                                $AllStats = "";
                                for ($i = 0; $i<count($obj->StatsTabEncoded); $i++) {
                                    $Value = $obj->StatsTabEncoded[$i];
                                    if($i != count($obj->StatsTabEncoded) - 1) 
                                    {
                                        echo $Value . ",";
                                    } 
                                    else
                                    {
                                        echo $Value . "";
                                    }
                                    $AllStats += $Value;
                                }
            echo('          </textarea>');
            echo('
                        </td>
                        <td>
                            <div id="statsViewer">');
                                foreach ($obj->StatsTab as $Value2) {
                                    $style = '';
                                    if(substr($Value2, 0, 1) == "-") {
                                        $style = 'color: red;';
                                    }
                                    echo '<span style="'.$style.'">' . $Value2 . '</span>';
                                }
            echo('          </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td align="right">SQL:</td>
                        <td id="gen_line" colspan="2">');
                                $SQL = new createQuery($obj, $AllStats);
                                echo ('<input type="text" value="'.$SQL->Query.'" />
                        </td>
                    </tr>
                    <tr>
                        <td align="right">
                            SWF:
                        </td>
                        <td id="gen_line" colspan="2">');
                                $SWF = new createSWF($obj);
                                echo ('<input type="text" value="'.$SWF->Line.'" />
                        </td>
                    </tr>
                </tfoot>
            </table>
        ');
    }
    $nextPage = $pageWant + 1;
    echo '<span style="display:block;margin-top: 10px;" align="center"><a href="?t='.$typeWant.'&p='.$nextPage.'">[ Page suivante ('.$nextPage.') ]</a></span>';
    echo '</div>';
?>