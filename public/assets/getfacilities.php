
<!DOCTYPE html>


<?php
$q = intval($_GET['q']);

$con = mysqli_connect('localhost','root','zaq12345','odkdash_two');
if (!$con) {
    die('Could not connect: ' . mysqli_error($con));
}

mysqli_select_db($con,"ajax_demo");
$sql="SELECT * FROM certification_facilities WHERE region = '".$q."'";
$result = mysqli_query($con,$sql);

    echo "<select ><option value=''>please choose an option</option>";
            
    foreach ($result as $res){
        
       echo "<option  value=".$res['id'].">". $res['facility_name'] ." </option> "; 
    }
   
    echo '</select>';

mysqli_close($con);
?>
</body>
</html>

