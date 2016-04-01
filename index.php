<?php
ignore_user_abort(true);
set_time_limit(0);
include("Kevin.php");

?>
<html>
<body>
<h2>Find Connection with "Kevin Bacon"</h2>

<form action="index.php">
<table>
<tr>
    <th>Actor Name:</th><th><input type="text" name="actor" value="<?php if(isset($_GET['actor'])){ echo $_GET['actor']; }?>"></th>
    <th></th><th><input type="submit" value="Find"></th>
</tr>
</table>
</form>

<?php
/*
//$input_name='Bruce Willis';

//$input_name='Tim Robbins';

//$input_name="Tim Robbins";
//$input_name="Robert Wagner";

//$input_name="Cécile De France";

//$input_name="Eric Tsang";

//$input_name="Morgan Freeman";

//$input_name="Chi Hung Ng";

//$input_name = "Eddie Griffin";
*/

if(isset($_GET['actor'])&&!empty($_GET['actor'])){

    $k=new Kevin;
    $k->run($_GET['actor']);
}

?>

</body>
</html>