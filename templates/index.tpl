<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title></title>
</head>
<body>
<img src="{$img|workshop:'uploads/':80x80}">
<form method="post" action="form.php" enctype="multipart/form-data">
    <input type="text" name="name">
    <input type="file" name="file">
    <input type="submit" name="go">
</form>
    {if $rows !== null}
    {foreach $rows as $row}
    <div>
        {$row.id}
        {$row.name}
    </div>
    {/foreach}
    {/if}
    <a href="export.php">export.php</a>
</body>
</html>
