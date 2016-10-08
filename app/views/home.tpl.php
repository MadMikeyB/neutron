<h1>The time is currently <?php echo $time; ?></h1>

<?php foreach ( $users as $user ): ?>
	<li><?php echo $user['username'] ?></li>
<?php endforeach ;?>