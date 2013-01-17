<script type="text/javascript">
/* fix for ajax-loading tooltips */
$('body').tooltip({ selector: '[rel=tooltip]' });
</script>
<?php

/**
 * Script to display all slave IP addresses and subnets in content div of subnets table!
 ***************************************************************************************/

/* get master subnet ID */
$subnetId = $_REQUEST['subnetId'];

/* get all slaves */
$slaves = getAllSlaveSubnetsBySubnetId ($subnetId);

/* get master details */
$master = getSubnetDetailsById($subnetId);

/* get section details */
$section = getSectionDetailsById($master['sectionId']);

/* print title */
$slaveNum = sizeof($slaves);
print "<h4>$master[description] (".transform2long($master['subnet'])."/$master[mask]) has $slaveNum directly nested subnets:</h4><hr><br>";

/* print HTML table */
print '<table class="slaves table table-striped table-condensed table-hover table-full table-top">'. "\n";

/* headers */
print "<tr>";
print "	<th class='small'>VLAN</th>";
print "	<th class='small description'>Subnet description</th>";
print "	<th>Subnet</th>";
print "	<th class='small'>Used</th>";
print "	<th class='small'>% free</th>";
print "	<th class='small'>Requests</th>";
print "	<th class='small'>Locked</th>";
print "</tr>";

/* print each slave */
$usedSum = 0;
$allSum = 0;

# for free space check
$slaveSize = sizeof($slaves);
$m = 0;

foreach ($slaves as $slave) {

	# if first check for free space
	if($m == 0) {
		# if master start != first slave start print free space
		if($master['subnet'] != $slave['subnet']) {
			# calculate diff
			$diff = gmp_strval(gmp_sub($slave['subnet'], $master['subnet']));
			
			print "<tr class='success'>";
			print "	<td></td>";
			print "	<td class='small description'>Free space</td>";
			print "	<td colspan='5'>". transform2long($master['subnet']) ." - ". transform2long(gmp_strval(gmp_add($master['subnet'], gmp_sub($diff,1)))) ." ( ".$diff." )</td>";
			print "</tr>";
		}
	}

	
	# reformat empty VLAN
	if(empty($slave['VLAN']) || $slave['VLAN'] == 0 || strlen($slave['VLAN']) == 0) { $slave['VLAN'] = "/"; }
	
	# get VLAN details
	$slave['VLAN'] = subnetGetVLANdetailsById($slave['vlanId']);
	$slave['VLAN'] = $slave['VLAN']['number'];
	
	print "<tr>";
    print "	<td class='small'>$slave[VLAN]</td>";
    print "	<td class='small description'><a href='subnets/$section[id]/$slave[id]/'>$slave[description]</a></td>";
    print "	<td><a href='subnets/$section[id]/$slave[id]/'>".transform2long($slave['subnet'])."/$slave[mask]</a></td>";
    
    # details
    $ipCount = countIpAddressesBySubnetId ($slave['id']);
	$calculate = calculateSubnetDetails ( gmp_strval($ipCount), $slave['mask'], $slave['subnet'] );
    print ' <td class="small">'. $calculate['used'] .'/'. $calculate['maxhosts'] .'</td>'. "\n";
    print '	<td class="small">'. $calculate['freehosts_percent'] .'</td>';
    
    # add to sum if IPv4
    if ( IdentifyAddress( $slave['subnet'] ) == "IPv4") {
		$usedSum = $usedSum + $calculate['used'];
		$allSum  = $allSum  + $calculate['maxhosts'];    
    }
	
	# allow requests
	if($slave['allowRequests'] == 1) 			{ print '<td class="allowRequests small">enabled</td>'; }
	else 										{ print '<td class="allowRequests small"></td>'; }
	
	# check if it is locked for writing
	if(isSubnetWriteProtected($slave['id'])) 	{ print '<td class="lock small"><i class="icon-gray icon-lock" rel="tooltip" title="Subnet is locked for writing for non-admins!"></i></td>'; } 
	else 										{ print '<td class="nolock small"></td>'; }

	print '</tr>' . "\n";
	
	
	# check if some free space between this and next subnet
	if(isset($slaves[$m+1])) {
		# get IP type
		if ( IdentifyAddress( $master['subnet'] ) == "IPv4") 	{ $type = 0; }
		else 													{ $type = 1; }
		# get max host for current
		$slave['maxip'] = gmp_strval(gmp_add(MaxHosts($slave['mask'],$type),2));
		# calculate diff
		$diff = gmp_strval(gmp_sub($slaves[$m+1]['subnet'], gmp_strval(gmp_add($slave['subnet'],$slave['maxip']))));
		
		# if diff print free space
		if($diff > 0) {
			print "<tr class='success'>";
			print "	<td></td>";
			print "	<td class='small description'>Free space</td>";
			print "	<td colspan='5'>". transform2long(gmp_strval(gmp_add($slave['maxip'], $slave['subnet']))) ." - ". transform2long(gmp_strval(gmp_add(gmp_add($slave['maxip'], $slave['subnet']), gmp_sub($diff,1)))) ." ( ".$diff." )</td>";
			print "</tr>";			
		}		
	}
	
	
	# next - for free space check
	$m++;	
	
	# if last check for free space
	if($m == $slaveSize) {
		# get IP type
		if ( IdentifyAddress( $master['subnet'] ) == "IPv4") 	{ $type = 0; }
		else 													{ $type = 1; }
		# calculate end of master and last slave
		$maxh_m = gmp_strval(gmp_add(MaxHosts( $master['mask'], $type ),2));
		$maxh_s = gmp_strval(gmp_add(MaxHosts( $slave['mask'],  $type ),2));
		
		$max_m  = gmp_strval(gmp_add($master['subnet'], $maxh_m));
		$max_s  = gmp_strval(gmp_add($slave['subnet'],  $maxh_s));
		
		$diff   = gmp_strval(gmp_sub($max_m, $max_s));
	
		# if slave stop < master stop print free space
		if($max_m > $max_s) {			
			print "<tr class='success'>";
			print "	<td></td>";
			print "	<td class='small description'>Free space</td>";
			print "	<td colspan='5'>". transform2long(gmp_strval(gmp_sub($max_m, $diff))) ." - ". transform2long(gmp_strval(gmp_sub($max_m, 1))) ." ( $diff )</td>";
			print "</tr>";
		}	
	}

}

# graph
include_once('subnetDetailsGraph.php');



print '</table>'. "\n";

?>