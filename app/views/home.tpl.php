<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">All Users</h3>
	</div>
	<div class="panel-body">
		<ul class="list-group">
		<?php foreach ($users as $user): ?>
			<li class="list-group-item"><?php echo $user['username'] ?></li>
		<?php endforeach; ?>
		</ul>
	</div>
</div>