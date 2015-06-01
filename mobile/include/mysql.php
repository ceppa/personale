<?
	class mysqlConnection
	{
		private $dbname="presenze";
		private $myhost="localhost";
		private $myuser="root";
		private $mypass="minair";
		private $mysqli;

		function __construct() 
		{
			$this->mysqli=new mysqli($this->myhost,$this->myuser,$this->mypass,$this->dbname);
			if (mysqli_connect_errno())
				die("Connect failed: ". mysqli_connect_error());
	
			$this->mysqli->query('SET NAMES utf8');
		}

		function __destruct()
		{
			$this->mysqli->close();
		}

		protected function my_die($message)
		{
			$fp = @fopen('error.txt', 'w+');
			if($fp)
			{
				fwrite($fp, "$message\n");
				fclose($fp);
			}
			else
				echo $message;
			die();
		}
	
		function insert_id()
		{
			return $this->conn->insert_id;
		}
	
		function do_query($query)
		{
			if(($result=$this->mysqli->query($query))===false)
				$this->my_die("$query<br>".$mysqli->error);
			return $result;
		}
	
		function result_to_array($result,$useid=true)
		{
			$out=array();
			while($row=$result->fetch_assoc())
			{
				if(isset($row["id"])&&$useid)
				{
					$id=$row["id"];
					unset($row["id"]);
					$out[$id]=$row;
				}
				else
					$out[]=$row;
			}
			$result->free_result();
			return $out;
		}
	}
?>
