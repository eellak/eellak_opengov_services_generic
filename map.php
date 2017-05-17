<?php

global $print; //copy from _scripts/mapper.php. don't know what it does, will document when find out.
				
include('functions.php');
include('mapper_functions.php');

$current_user=get_current_user($user_id);
$current_user_jurisdiction=get_user_jurisdiction($user_id);

if (can_edit_mappings($current_user)): ?>

get_available_users()
	
<div class="row">
	<div class="col-xs-12">
		
	</div>
</div>

<?php else: ?>

<div class="row">
	<div class="col-xs-12 alert alert-warning">
		<strong>Προσοχη! </strong>Δεν εχετε το δικαιωμα να επεξεργαστειτε αυτες τις πληροφοριες!
	</div>
</div>

<?php endif; ?>