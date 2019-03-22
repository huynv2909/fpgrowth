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
         // $conn = new mysqli('localhost', 'root', 'code', 'courses_crm');
         //
         // if ($conn->connect_error) {
         //     die("Kết nối thất bại: " . $conn->connect_error);
         // }
         // $sql = "SELECT * FROM parameter";
         //
         // // Thực thi câu truy vấn và gán vào $result
         // $result = mysqli_query($conn, $sql);
         //
         // // Kiểm tra số lượng record trả về có lơn hơn 0
         // // Nếu lớn hơn tức là có kết quả, ngược lại sẽ không có kết quả
         // if (mysqli_num_rows($result) > 0)
         // {
         //     $row = mysqli_fetch_assoc($result);
         //     $minCon = $row['value'];
         //
         //     $row = mysqli_fetch_assoc($result);
         //     $minSup = $row['value'];
         // }
         // $this->minSupport = $minSup;
         // $this->minConfidence = $minCon;
         //
         // $sql = "SELECT `student_id`, `course_id` FROM `tbl_student_courses`";
         //
         // $result = $conn->query($sql);
         //
         // $temp = array();
         //
         // if ($result->num_rows > 0)
         // {
         //     while($row = $result->fetch_assoc()) {
         //         if (array_key_exists($row['student_id'], $temp)) {
         //             $temp[$row['student_id']][] = $row['course_id'];
         //         } else {
         //             $temp[$row['student_id']] = array($row['course_id']);
         //         }
         //     }
         // }
         //
         // $this->frequentItem = $temp;

         $this->minSupport = 3;
         $this->minCon = 0;

         $this->frequentItem = array(
            '1' => array('f', 'a', 'c', 'd', 'g', 'i', 'm', 'p'),
            '2' => array('a', 'b', 'c', 'f', 'l', 'm', 'o'),
            '3' => array('b', 'f', 'h', 'j', 'o', 'w'),
            '4' => array('b', 'c', 'k', 's', 'p'),
            '5' => array('a', 'f', 'c', 'e', 'l', 'p', 'm', 'n')
         );

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

         // $conn->close();
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

            $sql = "INSERT INTO tree (id, course_id, quantity, parent_id)
                    VALUES ($id, $item, $quantity, $parent)";


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


   function mergeResult($arr1, $arr2) {
      foreach ($arr2 as $key => $value) {
         if (array_key_exists($key, $arr1)) {
            $arr1[$key] += $value;
         } else {
            $arr1[$key] = $value;
         }
      }
      return $arr1;
   }


   function getAllChild($parent_id, $tree) {
      $response = array();
      foreach ($tree as $node) {
         if ($node['parent_id'] == $parent_id) {
            $response[] = $node;
         }
      }
      return $response;
   }

   function countAll($parent_id, $tree) {
      $children = getAllChild($parent_id, $tree);
      if (count($children) > 0) {
         $temp = array();
         foreach ($children as $child) {
            $temp[] = mergeResult(array($tree[$parent_id]['course'] => $tree[$parent_id]['quantity']), countAll($child['id'], $tree));
         }

         $i = 0;
         $ret = array();
         foreach ($temp as $one) {
            $i++;
            if ($i > 1) {
               reset($one);
               $one[key($one)] = 0;
            }
            $ret = mergeResult($ret, $one);
         }

         return $ret;
      } else {
         // return array('item' => $tree[$parent_id]['course_id'], 'count' => $tree[$parent_id]['quantity']);
         return array($tree[$parent_id]['course'] => $tree[$parent_id]['quantity']);
      }

   }

   function countCombo($parent_id, $tree, $minSup, $minCon) {
      $children = getAllChild($parent_id, $tree);
      // print_r($children);
      // echo $minSup;
      // print_r($tree);
      if (count($children) > 0) {
         $temp = array();
         foreach ($children as $child) {
            if ($child['count'] >= $minSup) {
               $temp[] = mergeResult(array($tree[$parent_id]['item'] => $tree[$parent_id]['count']), countCombo($child['id'], $tree, $minSup, $minCon));
            }
            else {
               $temp[] = array($tree[$parent_id]['item'] => $tree[$parent_id]['count']);
            }
         }

         return $temp;

      } else {
         return array($tree[$parent_id]['item'] => $tree[$parent_id]['count']);
      }
   }

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
   // $fpgrowth->getFPTree();
   echo '<pre>';
   print_r($fpgrowth->FPTree);
   print_r(countAll(1, $fpgrowth->FPTree));

?>
