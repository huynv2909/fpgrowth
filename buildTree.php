<?php
	class FPGrowth
	{
		public $frequentItem;
		public $minSupport;
		public $minConfidence;
		public $supportCount;
		public $orderedFrequentItem;
		public $FPTree;
		function __construct($minSup = 200, $minCon = 0.06)
		{
			$this->minSupport = $minSup;
			$this->minConfidence = $minCon;
			$this->supportCount 	= array();
			$this->orderedFrequentItem = array();
			$this->frequentItem = array();

			$this->getTransactions();
		}

		public function getTransactions() {
			$conn = new mysqli('localhost', 'root', 'code', 'courses_crm');

			if ($conn->connect_error) {
			    die("Kết nối thất bại: " . $conn->connect_error);
			}
         $sql = "SELECT * FROM parameter";

         // Thực thi câu truy vấn và gán vào $result
         $result = mysqli_query($conn, $sql);

         // Kiểm tra số lượng record trả về có lơn hơn 0
         // Nếu lớn hơn tức là có kết quả, ngược lại sẽ không có kết quả
         if (mysqli_num_rows($result) > 0)
         {
             $row = mysqli_fetch_assoc($result);
             $minCon = $row['value'];

             $row = mysqli_fetch_assoc($result);
             $minSup = $row['value'];
         }
         $this->minSupport = $minSup;
         $this->minConfidence = $minCon;

			$sql = "SELECT `student_id`, `code` FROM `tbl_student_courses`, `tbl_courses` WHERE `tbl_student_courses`.`course_id` = `tbl_courses`.`id` ";

			$result = $conn->query($sql);

			$temp = array();

			if ($result->num_rows > 0)
			{
			    while($row = $result->fetch_assoc()) {
			        if (array_key_exists($row['student_id'], $temp)) {
			        		$temp[$row['student_id']][] = $row['code'];
			        } else {
						  	$temp[$row['student_id']] = array($row['code']);
					  }
			    }
			}

			$this->frequentItem = $temp;

         // $this->minSupport = 3;
         // $this->$minCon = 0;

			// $this->frequentItem = array(
			// 	'1' => array('f', 'a', 'c', 'd', 'g', 'i', 'm', 'p'),
			// 	'2' => array('a', 'b', 'c', 'f', 'l', 'm', 'o'),
			// 	'3' => array('b', 'f', 'h', 'j', 'o', 'w'),
			// 	'4' => array('b', 'c', 'k', 's', 'p'),
			// 	'5' => array('a', 'f', 'c', 'e', 'l', 'p', 'm', 'n')
			// );
			//
			// $this->frequentItem = array(
			// 	'1' => array('a', 'b'),
			// 	'2' => array('b', 'c', 'd'),
			// 	'3' => array('a', 'c', 'd', 'e'),
			// 	'4' => array('a', 'd', 'e'),
			// 	'5' => array('a', 'b', 'c'),
			// 	'6' => array('a', 'b', 'c', 'd'),
			// 	'7' => array('a'),
			// 	'8' => array('a', 'b', 'c'),
			// 	'9' => array('a', 'b', 'd'),
			// 	'10' => array('b', 'c', 'e')
			// );

			$conn->close();
		}

		public function get()
		{
			$this->show($this->frequentItem);
		}

		public function getFrequentItem()
		{
			$this->show($this->frequentItem);
		}

		public function orderFrequentItem($frequentItem, $supportCount)
		{
			foreach ($frequentItem as $id => $set) {
				$ordered 	= array();
				foreach ($supportCount as $key => $value) {
					if(in_array($key, $set))
					{
						$ordered[]	= $key;
					}
				}
				$this->orderedFrequentItem[$id]	= $ordered;
			}
		}

		public function getOrderedFrequentItem()
		{
			$this->show($this->orderedFrequentItem);
		}

		public function countSupportCount()
		{
			if(is_array($this->frequentItem))
			{
				foreach ($this->frequentItem as $set) {
					if(is_array($set))
					{
						foreach ($set as $item) {
							if (empty($this->supportCount[$item])) {
								$this->supportCount[$item] = 1;
							}else{
								$this->supportCount[$item] = $this->supportCount[$item] + 1;
							}
						}
					}
				}
			}
		}

		public function getSupportCount()
		{
			$this->show($this->supportCount);
		}

		public function orderBySupportCount()
		{
			arsort($this->supportCount);
		}

		public function removeByMinimumSupport($supportCount)
		{
			if(is_array($supportCount))
			{
				$this->supportCount = array();
				foreach ($supportCount as $key => $value) {
					if ($value >= $this->minSupport)
					{
						$this->supportCount[$key] = $value;
					}
				}
			}
		}


		public function buildFPTree($orderedFrequentItem)
		{
			$FPTree[] 	= array(
				'id' => 0,
				'item'	=> 'null',
				'count'	=> 0,
				'parent_id'	=> 'null',
			);

			$node_count = 1;
			foreach ($orderedFrequentItem as $set) {
				$current_node = 0;
				foreach ($set as $item) {
					// If node exist
					if ($this->isChild($item, $current_node, $FPTree, $node_id)) {
						$current_node = $node_id;
						$this->addToNode($node_id, $FPTree);
					} else {
						$new_node = array(
							'id' => $node_count,
							'item'	=> $item,
							'count'	=> 1,
							'parent_id'	=> $current_node
						);

						$FPTree[] = $new_node;
						$current_node = $node_count;

						$node_count++;
					}
				}
			}

			$this->FPTree = $FPTree;
		}

      public function saveTree() {
         // Tạo kết nối
         $conn = mysqli_connect('localhost', 'root', 'code', 'courses_crm');

         // Kiểm tra kết nối
         if (!$conn) {
             die("Kết nối thất bại: " . mysqli_connect_error());
         }

         $del_cmd = 'TRUNCATE TABLE tree';
         if (!mysqli_query($conn, $del_cmd)) {
             echo "Lỗi: " . $sql . "<br>" . mysqli_error($conn);
         }

         foreach ($this->FPTree as $node) {
            // Câu SQL Insert
            $id = $node['id'];
            $item = $node['item'];
            $quantity = $node['count'];
            $parent = $node['parent_id'];

            $sql = "INSERT INTO tree (id, course, quantity, parent_id)
                    VALUES ($id, '$item', $quantity, $parent)";


              // Thực hiện thêm record
              if (!mysqli_query($conn, $sql)) {
                  echo "Lỗi: " . $sql . "<br>" . mysqli_error($conn);
              }
         }

         // Ngắt kết nối
         mysqli_close($conn);
      }

		public function getFPTree()
		{
			$this->show($this->FPTree);
		}

		public function show($var) {
			echo "<pre>";
			print_r($var);
			echo "</pre>";
		}

		// Check if tree have item note in layer then return id node
		private function isChild($item, $parent_id, $tree, &$node_id) {
			foreach ($tree as $node) {
				if ($node['parent_id'] == $parent_id && $node['item'] == $item) {
					$node_id = $node['id'];
					return true;
				}
			}

			return false;
		}

		private function addToNode($node_id, &$tree) {
			foreach ($tree as $key => $node) {
				if ($node['id'] == $node_id) {
					$tree[$key]['count']++;
				}
			}
		}

	}


   if (isset($_POST['submit'])) {
      // Kết nối CSDL
      $conn = mysqli_connect('localhost', 'root', 'code', 'courses_crm');

      // Kiểm tra kết nối
      if (!$conn) {
          die("Kết nối thất bại: " . mysqli_connect_error());
      }

      // Lệnh update
      $sql1 = "UPDATE parameter SET value='" . $_POST['support'] . "' WHERE name='minSupport'";

      // Thực hiện update
      if (!mysqli_query($conn, $sql1)) {
          echo "Update thất bại 1: " . mysqli_error($conn);
      }

      // Lệnh update
      $sql2 = "UPDATE parameter SET value='" . $_POST['confidence'] . "' WHERE name='minConfidence'";

      // Thực hiện update
      if (!mysqli_query($conn, $sql2)) {
          echo "Update thất bại 2: " . mysqli_error($conn);
      }

      // ngắt kết nối
      mysqli_close($conn);

   	$fpgrowth 	= new FPGrowth();

   	$fpgrowth->countSupportCount();

   	// echo "Item | Support Count not ordered=================================================================================";
   	// $fpgrowth->getSupportCount();

   	$fpgrowth->orderBySupportCount();
   	// echo "Item | Support Count ordered======================================================================================";
   	// $fpgrowth->getSupportCount();

   	$fpgrowth->removeByMinimumSupport($fpgrowth->supportCount);
   	// echo "Item | Support Count remove support count < minimum support count=====================================================";
   	// $fpgrowth->getSupportCount();


   	$fpgrowth->orderFrequentItem($fpgrowth->frequentItem, $fpgrowth->supportCount);

   	// echo "Output Frequent 1-item ordered by support count on each item===================================================================================================================================";
   	// $fpgrowth->getOrderedFrequentItem();
   	//echo "FP Tree result display in array================================================================================================================================================================";
   	$fpgrowth->buildFPTree($fpgrowth->orderedFrequentItem);
   	$fpgrowth->saveTree();
   	// $fpgrowth->getFPTree();
   }

   // Kết nối CSDL
   $conn = mysqli_connect('localhost', 'root', 'code', 'courses_crm');

   $sql1 = 'SELECT COUNT(id) AS count FROM tbl_students';
   $result = mysqli_query($conn, $sql1);
   $row = mysqli_fetch_assoc($result);
   $student_quantity = $row['count'];

   $sql2 = 'SELECT COUNT(id) AS count FROM tbl_courses';
   $result = mysqli_query($conn, $sql2);
   $row = mysqli_fetch_assoc($result);
   $course_quantity = $row['count'];

   $sql3 = 'SELECT COUNT(id) AS count FROM tbl_student_courses';
   $result = mysqli_query($conn, $sql3);
   $row = mysqli_fetch_assoc($result);
   $rgt_quantity = $row['count'];


   // Kiểm tra kết nối
   if (!$conn) {
       die("Kết nối thất bại: " . mysqli_connect_error());
   }

   // Câu SQL lấy danh sách
   $sql = "SELECT * FROM parameter";

   // Thực thi câu truy vấn và gán vào $result
   $result = mysqli_query($conn, $sql);

   // Kiểm tra số lượng record trả về có lơn hơn 0
   // Nếu lớn hơn tức là có kết quả, ngược lại sẽ không có kết quả
   if (mysqli_num_rows($result) > 0)
   {
       $row = mysqli_fetch_assoc($result);
       $minCon = $row['value'];

       $row = mysqli_fetch_assoc($result);
       $minSup = $row['value'];
   }
   else {
       echo "Không có record nào";
   }

   // Get courses
   $sql = "SELECT * FROM tbl_courses";
   $result = mysqli_query($conn, $sql);
   $courses = array();
   if (mysqli_num_rows($result) > 0)
   {
       // Sử dụng vòng lặp while để lặp kết quả
       while($row = mysqli_fetch_assoc($result)) {
           $courses[] = $row;
       }
   }

   // ngắt kết nối
   mysqli_close($conn);

   $fp 	= new FPGrowth($minSup,$minCon);
   $fp->countSupportCount();
   $fp->orderBySupportCount();
   $fp->removeByMinimumSupport($fp->supportCount);
	// print_r($fp->supportCount);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>Dựng cây FP</title>
      <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
   </head>
   <body>
      <h1 class="text-center">Dựng cây FPGrowth</h1>
      <div class="container">
         <div class="row">
         <div class="col-sm-6" style="background-color: aliceblue">
            <h2 class="text-center">Hệ thống hiện tại có:</h2>
            <p class="text-center"><?php echo $student_quantity . ' học viên, ' . $course_quantity . ' khóa học và ' . $rgt_quantity . ' lượt mua khóa học.' ?></p>
            <h3 class="text-center">Số lượng học viên từng khóa học</h3>
            <?php foreach ($fp->supportCount as $course => $value): ?>
                  <p class="text-center"><?php echo $course . ' : ' . $value; ?></p>
            <?php endforeach; ?>
         </div>
         <div class="col-sm-6">
            <form method="POST">
              <div class="form-group row">
                <label for="colFormLabelLg" class="col-sm-6 col-form-label col-form-label-lg">minSupport</label>
                <div class="col-sm-6">
                  <input type="number" class="form-control form-control-lg" name="support" placeholder="Unit" value="<?php echo $minSup; ?>">
                </div>
              </div>
              <div class="form-group row">
                <label for="colFormLabelLg" class="col-sm-6 col-form-label col-form-label-lg">minConfidence</label>
                <div class="col-sm-6">
                  <input type="number" class="form-control form-control-lg" name="confidence" placeholder="%" value="<?php echo $minCon; ?>">
                </div>
              </div>
              <div class="form-group row">
                <div class="col-sm-5 offset-sm-4">
                  <button type="submit" class="btn btn-primary" name="submit">Change</button>
                </div>
              </div>
            </form>
				<a href="register.php">Dang ky</a>
				<a href="index.php">Cham soc</a>
         </div>
         </div>
      </div>

   </body>
</html>
