<?php
	// CLIENT ID 417807084917-rp9c70j31busprd888jmr124kbu94jln.apps.googleusercontent.com
	// CLIENT SECRET mKFJchi9_1p2Wm0VBOU3PXq5
	$d = new DataBase();
	
	// Főkönyv listák beviteli formokhoz;
	if(isset($_GET["accounts"])){
		if($_GET["accounts"]=="accounts"){
			if($_GET["type"] == "expense") {
				$d->get('select * from accounts where accid > 2000;');
				$string = "
				<select name='acc' id='acc' data-native-menu='false' data-theme='a'>
						<option>Válassz főkönyvet:</option>";
				while($r = $d->result->fetchArray()){
					$string .= "<option value='{$r["accid"]}'>{$r["accname"]}</option>";
				}
				echo $string."</select>";
			}
			else if($_GET["type"] == "income") {
				$d->get('select * from accounts where accid < 2000;');
				$string = "
				<select name='acc' id='acc' data-native-menu='false' data-theme='a'>
						<option>Válassz főkönyvet:</option>";
				while($r = $d->result->fetchArray()){
					$string .= "<option value='{$r["accid"]}'>{$r["accname"]}</option>";
				}
				echo $string."</select>";
			}
			else {
				$d->get('select * from accounts;');
				$string = "
				<select name='acc' id='acc' data-native-menu='false' data-theme='a'>
						<option>Válassz főkönyvet:</option>";
				while($r = $d->result->fetchArray()){
					$string .= "<option value='{$r["accid"]}'>{$r["accname"]}</option>";
				}
				echo $string."</select>";
			}
		}
		else {
			$d->get("select * from helpers where accid='{$_GET['accounts']}';");
			$string = "
			<select name='helpers' id='helpers' data-native-menu='false' data-theme='a'>
					<option>Válassz kategóriát:</option>";
			while($r = $d->result->fetchArray()){
				$string .= "<option value='{$r["description"]}'>{$r["description"]}</option>";
			}
			echo $string."</select>";
		}
	}
	
	// Előrejelzések
	if(isset($_GET["forecast"])){
		if($_GET["forecast"] == "forecast"){
			$d->get("select id, forecast.accid as accid, description, amount, accname from forecast left join accounts on accounts.accid = forecast.accid where month='{$_GET["period"]}' order by forecast.accid asc, description asc;");
			$string = "<ul data-role='listview' data-filter='true' data-filter-placeholder='Keresés' data-inset='true'>";
			while($r = $d->result->fetchArray()){
				$string .= "<li><a href=''><h2 class='".($r["amount"]<0?'red':'green')."'>{$r["amount"]}</h2><h4>{$r["accname"]} - {$r["description"]} </h4></a><a href='' class='edit' data-id='{$r["id"]};{$r["accid"]};{$r["description"]};{$r["amount"]}' data-icon='edit'></a></li>";
			}
			echo $string."</ul>";
		}
	}
	
	// Mérleg 
	if(isset($_GET["balance"])){
		$d->get("select sum(amount) as foreamount from forecast where month='{$_GET["period"]}';");
		$fc = $d->result->fetchArray();
		$d->get("select sum(amount) as amount from data where strftime('%Y%m', date)='{$_GET["period"]}';");
		$act = $d->result->fetchArray();
		$r = array();
		$a = array();
		$d->get('select accounts.accid as accid, description, accname from accounts left join helpers on helpers.accid=accounts.accid;');
		while($accounts = $d->result->fetchArray()){
			array_push($r, array("accid"=>$accounts["accid"], "accname"=>$accounts["accname"], "description"=>$accounts["description"], "foreamount"=>0, "amount"=>0));
			$a[$accounts["accid"]] = array("accid"=>$accounts["accid"], "accname"=>$accounts["accname"], "foreamount"=>0, "amount"=>0);
		}
		$d->get("select accid, sum(amount) as amount, description from data where strftime('%Y%m', date) ='{$_GET["period"]}' group by accid, description");
		while($actual = $d->result->fetchArray()){
			for($i=0;$i<count($r);$i++){
				if($r[$i]["accid"]==$actual["accid"] && $r[$i]["description"]==$actual["description"]){
					$r[$i]["amount"] = $actual["amount"];
				}
			}
			if($a[$actual["accid"]]["accid"]==$actual["accid"]){
				$a[$actual["accid"]]["amount"] += $actual["amount"];
			}
		}
		$d->get("select accid, sum(amount) as foreamount, description from forecast where month ='{$_GET["period"]}' group by accid, description");
		while($forc = $d->result->fetchArray()){
			for($i=0;$i<count($r);$i++){
				if($r[$i]["accid"]==$forc["accid"] && $r[$i]["description"]==$forc["description"]){
					$r[$i]["foreamount"] = $forc["foreamount"];
				}
			}
			if($a[$forc["accid"]]["accid"]==$forc["accid"]){
				$a[$forc["accid"]]["foreamount"] += $forc["foreamount"];
			}
		}
		$string = "
			<br /><br />
			<ul data-role='listview' data-inset='false'>
				<li data-role='list-divider'>
					<h6>
					<div class='ui-grid-b'>
						<div class='ui-block-a trunc'><span class='ui-mini left'>Megnevezés</span></div>
						<div class='ui-block-b'><span class='ui-mini right fc'>Előrejelzés</span></div>
						<div class='ui-block-c'><spna class='ui-mini right act'>Aktuális</span></div>
					</div>
					</h6>
				</li>
				<li data-role='list-divider'>
				<h6>
				<div class='ui-grid-b'>
					<div class='ui-block-a trunc'><span class='ui-mini left'>Összesen:</span></div>
					<div class='ui-block-b'><strong><span class='ui-mini right ".($fc["foreamount"]<0?'red':'green')."'>{$fc["foreamount"]}</span></strong></div>
					<div class='ui-block-c'><strong><span class='ui-mini right ".($act["amount"]<0?'red':'green')."'>{$act["amount"]}</span></strong></div>
				</div>
				</h6>
				</li>
			</ul>
			<div data-role='collapsibleset' data-inset='false'>
			";
			foreach($a as $k=>$v){
				$string .= "
				<div data-role='collapsible'>
					<h6>
						<div class='ui-grid-b'>
							<div class='ui-block-a trunc'><span class='ui-mini left'>{$v["accname"]}</span></div>
							<div class='ui-block-b'><span class='ui-mini right fc'>{$v["foreamount"]}</span></div>
							<div class='ui-block-c'><span class='ui-mini right act'>{$v["amount"]}</span></div>
						</div>
					</h6>
					<ul data-role='listview' data-inset='false'>
					";
					for($i=0;$i<count($r);$i++){
						if($r[$i]["accid"]==$v["accid"]){
							$string .= "
							<li>
								<div class='ui-grid-b'>
									<div class='ui-block-a trunc'><span class='ui-mini left'>{$r[$i]["description"]}</span></div>
									<div class='ui-block-b'><span class='ui-mini right fc'>{$r[$i]["foreamount"]}</span></div>
									<div class='ui-block-c'><span class='ui-mini right act'>{$r[$i]["amount"]}</span></div>
								</div>
							</li>";
						}
					}
				$string .= "
					</ul>
				</div>";
			}
		$string .="</div>";
		echo $string;
	}
	
	// Bevételek
	if(isset($_GET["incomes"])){
		if($_GET["incomes"]=="incomes"){
			$d->get('select id, date, data.accid, description, amount, accname from data left join accounts on data.accid=accounts.accid where data.accid < 2000 and strftime("%m", date) == "'.date('m').'" order by data.id desc;');
			$string = "<ul data-role='listview' data-filter='true' data-filter-placeholder='Keresés' data-inset='true'>";
			while($r = $d->result->fetchArray()){
				$string .= "<li><a href=''><h2 class='green'>{$r["amount"]}</h2><h4>{$r["accname"]} - {$r["description"]} </h4><p class='ui-li-aside'>{$r["date"]}</p></a><a href='' class='edit' data-id='{$r["id"]};{$r["accid"]};{$r["description"]};{$r["amount"]}' data-icon='edit'></a></li>";
			}
			echo $string."</ul>";
		}
		else {
			$a = explode(";", $_GET["incomes"]);
			$d->get('select * from accounts where accid < 2000;');
			$string = "
			<select name='acc' id='acc' data-native-menu='false' data-theme='c'>
					<option>Válassz főkönyvet:</option>";
			while($r = $d->result->fetchArray()){
				if($a[1] == $r["accid"])
					$string .= "<option selected='selected' value='{$r["accid"]}'>{$r["accname"]}</option>";
				else
					$string .= "<option value='{$r["accid"]}'>{$r["accname"]}</option>";
			}
			$string .= "</select>";
			$d->get("select * from helpers where accid='{$a[1]}';");
			$string .= "
			<select name='helpers' id='helpers' data-native-menu='false' data-theme='c'>
					<option>Válassz kategóriát:</option>";
			while($r = $d->result->fetchArray()){
				if($a[2] == $r["description"])
					$string .= "<option selected = 'selected' value='{$r["description"]}'>{$r["description"]}</option>";
				else
					$string .= "<option value='{$r["description"]}'>{$r["description"]}</option>";
			}
			$string .= "</select>
			<label for='amount'>Összeg:</label>
			<input name='amount' id='amount' type='number' placeholder='0' value='{$a[3]}'>
			<input type='hidden' id='id' value='{$a[0]}'>
			<hr>
			<a id='delete' class='ui-btn ui-icon-delete ui-btn-icon-top ui-corner-all ui-btn-b'>Törlés</a>
			";
			echo $string;
		}
	}
	
	// Kiadások
	if(isset($_GET["expenses"])){
		if($_GET["expenses"]=="expenses"){
			$d->get('select id, date, data.accid, description, amount, accname from data left join accounts on data.accid=accounts.accid where data.accid > 2000 and strftime("%m", date) == "'.date('m').'" order by data.id desc;');
			$string = "<ul data-role='listview' data-filter='true' data-filter-placeholder='Keresés' data-inset='true'>";
			while($r = $d->result->fetchArray()){
				$string .= "<li><a href=''><h2 class='red'>{$r["amount"]}</h2><h4>{$r["accname"]} - {$r["description"]} </h4><p class='ui-li-aside'>{$r["date"]}</p></a><a href='' class='edit' data-id='{$r["id"]};{$r["accid"]};{$r["description"]};{$r["amount"]}' data-icon='edit'></a></li>";
			}
			echo $string."</ul>";
		}
		else {
			$a = explode(";", $_GET["expenses"]);
			$d->get('select * from accounts where accid > 2000;');
			$string = "
			<select name='acc' id='acc' data-native-menu='false' data-theme='b'>
					<option>Válassz főkönyvet:</option>";
			while($r = $d->result->fetchArray()){
				if($a[1] == $r["accid"])
					$string .= "<option selected='selected' value='{$r["accid"]}'>{$r["accname"]}</option>";
				else
					$string .= "<option value='{$r["accid"]}'>{$r["accname"]}</option>";
			}
			$string .= "</select>";
			$d->get("select * from helpers where accid='{$a[1]}';");
			$string .= "
			<select name='helpers' id='helpers' data-native-menu='false' data-theme='b'>
					<option>Válassz kategóriát:</option>";
			while($r = $d->result->fetchArray()){
				if($a[2] == $r["description"])
					$string .= "<option selected = 'selected' value='{$r["description"]}'>{$r["description"]}</option>";
				else
					$string .= "<option value='{$r["description"]}'>{$r["description"]}</option>";
			}
			$string .= "</select>
			<label for='amount'>Összeg:</label>
			<input name='amount' id='amount' type='number' placeholder='0' value='".substr($a[3],1)."'>
			<input type='hidden' id='id' value='{$a[0]}'>
			<hr>
			<a id='delete' class='ui-btn ui-icon-delete ui-btn-icon-top ui-corner-all ui-btn-b'>Törlés</a>
			";
			echo $string;
		}
	}
	
	// Segédadatok
	if(isset($_GET["helper"])){
		if($_GET["helper"]=="helper"){
			$d->get("select id, helpers.accid as accid, description, accname from helpers left join accounts on accounts.accid=helpers.accid order by helpers.accid asc, description asc;");
			$string = "<ul data-role='listview' data-filter='true' data-filter-placeholder='Keresés' data-inset='true'>";
			while($r = $d->result->fetchArray()){
				$string .= "<li><a href=''><h6>{$r["description"]}</h6><p class='ui-li-aside'>{$r["accname"]}</p></a><a href='' class='edit' data-id='{$r["id"]};{$r["accid"]};{$r["description"]}' data-icon='edit'></a></li>";
			}
			echo $string."</ul>";
		}
		else {
			$a = explode(";", $_GET["helper"]);
			$d->get('select * from accounts;');
			$string = "
			<select name='acc' id='acc' data-native-menu='false' data-theme='a'>
					<option>Válassz főkönyvet:</option>";
			while($r = $d->result->fetchArray()){
				if($a[1] == $r["accid"])
					$string .= "<option selected='selected' value='{$r["accid"]}'>{$r["accname"]}</option>";
				else
					$string .= "<option value='{$r["accid"]}'>{$r["accname"]}</option>";
			}
			$string .= "</select>
				<input name='description' id='description' type='text' placeholder='Megnevezés' value='{$a[2]}'>
				<input type='hidden' id='id' value='{$a[0]}'>
				<hr>
				<a id='delete' class='ui-btn ui-icon-delete ui-btn-icon-top ui-corner-all ui-btn-b'>Törlés</a>
			";
			echo $string;
		}
	}
	
	// Főkönyvek
	if(isset($_GET["account"])){
		if($_GET["account"]=="account"){
			$d->get('select accid, accname from accounts order by accid asc;');
			$string = "<ul data-role='listview' data-filter='true' data-filter-placeholder='Keresés' data-inset='true'>";
			while($r = $d->result->fetchArray()){
				$string .= "<li><a href=''><h6>{$r["accname"]}</h6><p class='ui-li-aside'>{$r["accid"]}</p></a><a href='' class='edit' data-id='{$r["accid"]};{$r["accname"]}' data-icon='edit'></a></li>";
			}
			echo $string."</ul>";
		}
		else {
			$a = explode(";", $_GET["account"]);
			$string = "
				<label for='accid'>Szám:</label>
				<input type='text' name='accid' id='accid' value='{$a[0]}'>
				<label for='accname'>Megnevezés:</label>
				<input type='text' name='accname' id='accname' value='{$a[1]}'>
				<a id='delete' class='ui-btn ui-icon-delete ui-btn-icon-top ui-corner-all ui-btn-b'>Törlés</a>
			";
			echo $string;
		}
	}
	
	// Tranzakciók
	if(isset($_POST["movement"])){
		if($_POST["movement"]=="newexpense"){
			$d->CUD("insert into data (date, accid, description, amount) VALUES ('".date("Y-m-d")."', '{$_POST["accid"]}', '{$_POST["description"]}', '-{$_POST["amount"]}');", "#kiadasok");
		}
		else if($_POST["movement"]=="newincome"){
			$d->CUD("insert into data (date, accid, description, amount) VALUES ('".date("Y-m-d")."', '{$_POST["accid"]}', '{$_POST["description"]}', '{$_POST["amount"]}');", "#bevetelek");
		}
		else if($_POST["movement"]=="newaccount"){
			$d->CUD("insert into accounts (accid, accname) VALUES ('{$_POST["accid"]}', '{$_POST["accname"]}');", "#accounts");
		}
		else if($_POST["movement"]=="newhelper"){
			$d->CUD("insert into helpers (accid, description) VALUES ('{$_POST["accid"]}', '{$_POST["description"]}');", "#helpers");
			$d->CUD("insert into forecast (month, accid, description, amount) VALUES('".date("Ym")."', '{$_POST["accid"]}', '{$_POST["description"]}', 0);");
		}
		else if($_POST["movement"] == "editexpense"){
			$d->CUD("update data set accid='{$_POST["accid"]}', description='{$_POST["description"]}', amount='-{$_POST["amount"]}' where id='{$_POST["id"]}';", "#kiadasok");
		}
		else if($_POST["movement"] == "editincome"){
			$d->CUD("update data set accid='{$_POST["accid"]}', description='{$_POST["description"]}', amount='{$_POST["amount"]}' where id='{$_POST["id"]}';", "#bevetelek");
		}
		else if($_POST["movement"] == "editaccount"){
			$d->CUD("update accounts set accid='{$_POST["accid"]}', accname='{$_POST["accname"]}' where accid='{$_POST["accid"]}';", "#accounts");
		}
		else if($_POST["movement"] == "edithelper"){
			$d->CUD("update helpers set accid='{$_POST["accid"]}', description='{$_POST["description"]}' where id='{$_POST["id"]}';", "#helpers");
		}
		else if($_POST["movement"] == "deleteincome"){
			$d->CUD("delete from data where id='{$_POST["id"]}';", "#bevetelek");
		}
		else if($_POST["movement"] == "deleteexpense"){
			$d->CUD("delete from data where id='{$_POST["id"]}';", "#kiadasok");
		}
		else if($_POST["movement"] == "deleteaccount"){
			$d->CUD("delete from accounts where accid='{$_POST["id"]}';", "#accounts");
		}
		else if($_POST["movement"] == "deletehelper"){
			$d->CUD("delete from helpers where id='{$_POST["id"]}';", "#helpers");
		}
		else if($_POST["movement"] == "editforecast"){
			$d->CUD("update forecast set amount='{$_POST["amount"]}' where id='{$_POST["id"]}';", "#forecast");
		}
		else if($_POST["movement"] == "setforecast"){
			$a = explode("],[", substr($_POST["data"],2 , strlen($_POST["data"])-4));
			$period = substr($a[0], -7, 6);
				$d->get("select * from forecast where month='$period';");
				if(count($d->result->fetchArray()) > 1){
					$string = "";
					for ($i=0;$i<count($a);$i++){
						$k = explode(",", $a[$i]);
						$string = "update forecast set amount={$k[2]} where month={$k[3]} and accid={$k[0]} and description={$k[1]}; ";
						$string = str_replace("\"", "'", $string);
						$d->CUD($string, "#forecast");
					}
					//$d->CUD($string, "#forecast");
				}
				else {
					$string = "";
					for ($i=0;$i<count($a);$i++){
						$k = explode(",", $a[$i]);
						$string = "insert into forecast (accid, description, amount, month) VALUES ({$k[0]}, {$k[1]}, {$k[2]}, {$k[3]}); ";
						$string = str_replace("\"", "'", $string);
						$d->CUD($string, "#forecast");
					}
				}
		}
	}
	
class DataBase{
	var $db;
	var $result;
	function __construct() {
		try{
			$this->db = new SQLite3('data', SQLITE3_OPEN_READWRITE);
		}
		catch (Exception $e){
			echo $e->getMessage();
		}
	}
	// DATA mainpulations
	function CUD($str, $loc){
		try {
			$this->db->exec($str);
			echo "<script>location.reload();</script>";
		}
		catch (Exception $e) {
			echo $e->getMessage();
		}
	}
	function get($str){
		try {
			$this->result = $this->db->query($str);
		}
		catch (Exception $e) {
			echo $e->getMessage();
		}
	}
}	
?>