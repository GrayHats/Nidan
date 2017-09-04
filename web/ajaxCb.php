<?php

include __DIR__.'/common.inc.php';

if(isset($_GET["action"])) {
    $ajax_action = sanitize($_GET["action"]);
} else if(isset($_POST["action"])) {
    $ajax_action = sanitize($_POST["action"]);
}

if($mySession->isLogged()) {
    /* ===========================================
    Add or edit AGENTS
    =========================================== */
    if($ajax_action == "agent_edit") {
	if(isset($_GET["id"])) {
	    $agent_id = intval($_GET["id"]);

	    $agent = new Agent($agent_id);
	}
	echo "<form method='POST' id='ajaxDialog'>
	<input type='hidden' name='action' value='cb_agent_edit'>";
	if(isset($agent_id)) {
	    echo "<input type='hidden' name='agent_id' value='$agent_id'>";
	    $agent_apikey = $agent->apiKey;
	} else {
	    // Compute a random API Key for this agent
	    $agent_apikey = md5(APG(16));
	}
	echo "<div class='form-group'>
	    <span class='form-group-addon'>Name</span>
	    <input type='text' id='agent_name' name='agent_name' class='w-100 validate[required]' value='$agent->name'>
	    <p class='help-block'>An arbitrary name for this agent</p>
	</div><div class='form-group'>
	    <span class='form-group-addon'>API Key</span>
	    <input type='text' id='agent_apikey' name='agent_apikey' class='w-100 validate[required]' value='$agent_apikey' readonly>
	    <p class='help-block'>Remember to copy this key to agent's nidan.cfg file !</p>
	</div><div class='form-group'>
	    <span class='form-group-addon'>Description</span>
	    <input type='text' id='agent_desc' name='agent_desc' class='w-100' value='$agent->description'>
	</div><div class='form-group'>
	    <input type='checkbox' name='is_enable' ".isChecked($agent->isEnable)."> Agent enabled
	    <p class='help-block'>If checked, this agent will be used for job(s)</p>
	</div>";
    }

    /* ===========================================
    Add or edit NETWORKS
    =========================================== */
    if($ajax_action == "network_edit") {
	if(isset($_GET["id"])) {
	    $net_id = intval($_GET["id"]);

	    $result = doQuery("SELECT ID,Network,Description,checkCycle,agentId,isEnable FROM Networks WHERE ID='$net_id';");
	    if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		$net_id = $row["ID"];
		$net_address = stripslashes($row["Network"]);
		$net_desc = stripslashes($row["Description"]);
		$net_checkcycle = intval($row["checkCycle"]);
		$net_agentid = $row["agentId"];
		$net_isenable = $row["isEnable"];
	    }
	}

	echo "<form method='POST' id='ajaxDialog'>
	<input type='hidden' name='action' value='cb_network_edit'>";
	if(isset($net_id)) {
	    echo "<input type='hidden' name='net_id' value='$net_id'>";
	}
	echo "<div class='form-group'>
	    <span class='form-group-addon'>Network</span>
	    <input type='text' id='net_address' name='net_address' class='w-100 validate[required]' value='$net_address'>
	    <p class='help-block'>Network address in CIDR notation (ie. 192.168.0.0/24)</p>
	</div><div class='form-group'>
	    <span class='form-group-addon'>Agent:</span>
	    <select data-placeholder='Choose which agent should scan this network' class='form-control' id='net_agentid' name='net_agentid'>
		<option value='0' ".isSelected(0,$net_agentid).">Any</option>";
	$result = doQuery("SELECT ID,Name FROM Agents WHERE isEnable=1;");
	if(mysqli_num_rows($result) > 0) {
	    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
		$agent_id = $row["ID"];
		$agent_name = stripslashes($row["Name"]);
		echo "<option value='$agent_id' ".isSelected($agent_id,$net_agentid).">$agent_name</option>";
	    }
	}
	echo "</select>
	</div><div class='form-group'>
	    <span class='form-group-addon'>Description</span>
	    <input type='text' id='net_desc' name='net_desc' class='w-100 validate[required]' value='$net_desc'>
	    <p class='help-block'>A quick description (ie. 'Home net')</p>
	</div><div class='form-group'>
	    <span for='net_checkcycle'>Check every (default 10) minutes:</label>
	    <input type='text' id='net_checkcycle' name='net_checkcycle' class='w-100 validate[required]' value='".(empty($net_checkcycle) ? "10":$net_checkcycle)."'>
	</div><div class='form-group'>
	    <input type='checkbox' name='is_enable' ".isChecked($net_isenable)."> Enabled
	    <p class='help-block'>If checked, this network will be scanned</p>
	</div></form>";
    }
    /* ===========================================
    Remove NETWORK
    =========================================== */
    if($ajax_action == "network_remove") {
	if(isset($_GET["id"])) {
	    $net_id = intval($_GET["id"]);

	    $result = doQuery("SELECT ID,Network,Description,checkCycle,isEnable FROM Networks WHERE ID='$net_id';");
	    if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		$net_id = $row["ID"];
		$net_address = stripslashes($row["Network"]);
		echo "<form method='POST' id='ajaxDialog'>
		    <input type='hidden' name='action' value='cb_network_remove'>
		    <input type='hidden' name='net_id' value='$net_id'>
		    <div class='form-group'>
			<h4>Are you sure ?</h4>
			<p>You are going to <b>remove network $net_address and all its hosts and services</b>.</p>
			<p>Please note: this operation cannot be undone.</p>
		    </div>
    		</form>";
	    }
	}
    }
    /* ===========================================
    Refresh NETWORK
    =========================================== */
    if($ajax_action == "network_refresh") {
	// TODO
    }
    /* ===========================================
    Refresh HOST
    =========================================== */
    if($ajax_action == "host_refresh") {
	// TODO
    }
    /* ===========================================
    Clean JOB queue - Remove old complete jobs
    =========================================== */
    if($ajax_action == "job_clean") {
	doQuery("DELETE FROM JobsQueue WHERE startDate IS NOT NULL AND endDate IS NOT NULL;");
	echo "Completed jobs cleared successfully !";
    }
    /* ===========================================
    Add or edit TRIGGER
    =========================================== */
    if($ajax_action == "trigger_edit") {
	if(isset($_GET["id"])) {
	    $trigger_id = intval($_GET["id"]);

	    $result = doQuery("SELECT Event,agentId,Action,Priority,Args,isEnable FROM Triggers WHERE userId='$mySession->userId' AND ID='$trigger_id';");
	    if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		$trigger_event = stripslashes($row["Event"]);
		$trigger_agentid = $row["agentId"];
		$trigger_action = stripslashes($row["Action"]);
		$trigger_priority = stripslashes($row["Priority"]);
		$trigger_args = stripslashes($row["Args"]);
		$trigger_isenable = $row["isEnable"];
	    }
	}

	echo "<form method='POST' id='ajaxDialog'>
	<input type='hidden' name='action' value='cb_trigger_edit'>";
	if(isset($trigger_id)) {
	    echo "<input type='hidden' name='trigger_id' value='$trigger_id'>";
	}
	echo "<div class='form-group'>
	    <span class='form-group-addon'>Agent:</span>
	    <select data-placeholder='Choose agent' class='form-control' id='trigger_agentid' name='trigger_agentid'>
		<option value='0' ".isSelected(0,$trigger_agentid).">Any</option>";
	$result = doQuery("SELECT ID,Name FROM Agents WHERE isEnable=1;");
	if(mysqli_num_rows($result) > 0) {
	    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
		$agent_id = $row["ID"];
		$agent_name = stripslashes($row["Name"]);
		echo "<option value='$agent_id' ".isSelected($agent_id,$trigger_agentid).">$agent_name</option>";
	    }
	}
	echo "</select>
	</div><div class='form-group'>
	    <span class='form-group-addon'>Event:</span>
	    <select data-placeholder='Choose event' class='form-control' id='trigger_event' name='trigger_event'>
		<option value='new_host' ".isSelected('new_host',$trigger_event).">New host detected</option>
		<option value='new_service' ".isSelected('new_service',$trigger_event).">New service detected</option>
		<option value='host_change' ".isSelected('host_change',$trigger_event).">Host changed</option>
		<option value='host_offline' ".isSelected('host_offline',$trigger_event).">Host offline</option>
		<option value='host_online' ".isSelected('host_online',$trigger_event).">Host online</option>
		<option value='service_down' ".isSelected('service_down',$trigger_event).">Service down</option>
		<option value='service_up' ".isSelected('service_up',$trigger_event).">Service up</option>
		<option value='agent_start' ".isSelected('agent_start',$trigger_event).">Agent start</option>
		<option value='agent_stop' ".isSelected('agent_stop',$trigger_event).">Agent stop</option>
		<option value='job_start' ".isSelected('job_start',$trigger_event).">Job start</option>
		<option value='job_error' ".isSelected('job_error',$trigger_event).">Job error</option>
		<option value='job_end' ".isSelected('job_end',$trigger_event).">Job end</option>
	    </select>
	</div><div class='form-group'>
	    <span class='form-group-addon'>Action:</span>
	    <select data-placeholder='Choose action when the event happen' class='form-control' id='trigger_action' name='trigger_action'>
		<option value='none' ".isSelected('none',$trigger_action).">Nothing</option>
		<option value='sendmail' ".isSelected('sendmail',$trigger_action).">Send mail</option>
	    </select>
	</div><div class='form-group'>
	    <span class='form-group-addon'>Notify</span>
	    <select data-placeholder='Choose the notification time for this trigger' class='form-control' id='trigger_priority' name='trigger_priority'>
		<option value='asap' ".isSelected('asap',$trigger_priority).">As soon as possible</option>
		<option value='hourly' ".isSelected('hourly',$trigger_priority).">Hourly</option>
		<option value='daily' ".isSelected('daily',$trigger_priority).">Daily</option>
		<option value='weekly' ".isSelected('weekly',$trigger_priority).">Weekly</option>
	    </select>
	</div><div class='form-group'>
	    <span class='form-group-addon'>Args (separated by comma)<span>
	    <input type='text' id='trigger_args' name='trigger_args' class='form-control w-100' value='$trigger_args'>
	    <p class='help-block'>Action arguments, like email address...</p>
	</div><div class='form-group'>
	    <input type='checkbox' name='is_enable' ".isChecked($trigger_isenable)."> Enabled
	    <p class='help-block'>If checked, this trigger is enable</p>
	</div></form>";
    }
    /* ===========================================
    TABLES JSON Data
    =========================================== */
    if($ajax_action == "table_get_jobs") {

	$result = doQuery("SELECT COUNT(*) AS Total FROM JobsQueue;");
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	
	$total_rows = intval($row["Total"]);
	
    	$order_by = strtoupper(sanitize($_GET["order"]));
	$offset = intval($_GET["offset"]);
	$limit = intval($_GET["limit"]);

	$result = doQuery("SELECT Job,itemId,agentId,Args,addDate,startDate,endDate,timeElapsed FROM JobsQueue ORDER BY addDate $order_by LIMIT $limit OFFSET $offset;");
	if(mysqli_num_rows($result) > 0) {
	    $ret_array = array("total" => $total_rows);

	    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {

		$job_method = stripslashes($row["Job"]);
		$job_id = $row["itemId"];
		$job_agent_id = ($row["agentId"] ? $row["agentId"] : "Any");

		$job_adddate = new DateTime($row["addDate"]);

		$job_startdate = false;
		if($row["startDate"]) {
		    $job_startdate = new DateTime($row["startDate"]);
		}

		$job_enddate = false;
		if($row["endDate"]) {
		    $job_enddate = new DateTime($row["endDate"]);
		}

		$job_timeelapsed = $row["timeElapsed"];

		$ret_array["rows"][] = array("job" => $job_method, "id" => $job_id, "agent_id" => $job_agent_id, "add_date" => $job_adddate->format("H:i:s d-M-Y"), "start_date" => ($job_startdate ? $job_startdate->format("H:i:s d-M-Y") : "Not yet"), "end_date" => ($job_enddate ? $job_enddate->format("H:i:s d-M-Y") : "Not yet"), "time_elapsed" => $job_timeelapsed);
	    }
	    header('Content-Type: application/json');
	    $json = json_encode($ret_array);
	    echo $json;
	}
    }

    if($ajax_action == "table_get_eventlog") {
	$result = doQuery("SELECT COUNT(*) AS Total FROM EventsLog;");
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	
	$total_rows = intval($row["Total"]);
	
    	$order_by = strtoupper(sanitize($_GET["order"]));
	$offset = intval($_GET["offset"]);
	$limit = intval($_GET["limit"]);

	$result = doQuery("SELECT addDate,Event,Args FROM EventsLog ORDER BY addDate $order_by LIMIT $limit OFFSET $offset;");
	if(mysqli_num_rows($result) > 0) {
	    $ret_array = array("total" => $total_rows);

	    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
    		$log_adddate = new DateTime($row["addDate"]);
		$log_event = stripslashes($row["Event"]);
		$log_args = $row["Args"];

		$ret_array["rows"][] = array("add_date" => $log_adddate->format("H:i:s d-M-Y"),"event" => $log_event,"args" => $log_args);
	    }

	    header('Content-Type: application/json');
	    $json = json_encode($ret_array);
	    echo $json;
	}
    }
}

?>
    