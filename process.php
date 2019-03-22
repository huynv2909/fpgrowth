<?php
   function isCov($course, $course_cov, $compare) {
      $conn = mysqli_connect('localhost', 'root', 'code', 'courses_crm');
      // Kiểm tra kết nối
      if (!$conn) {
          die("Kết nối thất bại: " . mysqli_connect_error());
      }

      $sql = "SELECT COUNT(id) AS count FROM tbl_student_courses
               WHERE course_id =
                  (SELECT id FROM tbl_courses WHERE code = '$course_cov')
                AND student_id NOT IN
               (SELECT DISTINCT student_id FROM tbl_student_courses WHERE course_id IN
                  (SELECT id FROM tbl_courses WHERE code = '$course')
               )";
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      $have_course = $row['count'];

      $sql = "SELECT COUNT(id) AS count FROM tbl_students
               WHERE id NOT IN
                  (SELECT student_id FROM tbl_student_courses
                        WHERE course_id IN
                        (SELECT id FROM tbl_courses WHERE code = '$course')
                  )";
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      $total = $row['count'];

      mysqli_close($conn);
      return ($have_course/$total < $compare);
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

   // Count all item in parent_id
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

   function getCombo($parent_id, $tree, $minSup, $minCon) {
      $children = getAllChild($parent_id, $tree);

      if (count($children) > 0) {
         $rs1 = array();
         $rs = array();
         foreach ($children as $child) {
            $all = countAll($child['id'], $tree);
            arsort($all);
            if (count($all['relate']) > 0) {
               foreach ($all['relate'] as $key => $value) {
                  if ($value < $minSup) {
                     break;
                  } else {
                     $rs1[] = array_merge(array($key => $value), getCombo($child['id'], $tree, $minSup, $minCon));
                  }
               }
            }
            $rs[] = $rs1;
         }
         return $rs;
      } else {
         // return array('item' => $tree[$parent_id]['course_id'], 'count' => $tree[$parent_id]['quantity']);
         return array();
      }
   }

   function getAssociation($course, $minSup, $minCon) {
      // Kết nối CSDL
      $conn = mysqli_connect('localhost', 'root', 'code', 'courses_crm');

      // Kiểm tra kết nối
      if (!$conn) {
          die("Kết nối thất bại: " . mysqli_connect_error());
      }

      $sql = "SELECT * FROM tree WHERE course = '$course'";
      $result = $conn->query($sql);

      $course_nodes = array();
      if ($result->num_rows > 0)
      {
          while($row = $result->fetch_assoc()) {
              $course_nodes[] = $row;
          }
      }

      $sql = "SELECT * FROM tree";
      $result = $conn->query($sql);

      $tree = array();
      if ($result->num_rows > 0)
      {
          while($row = $result->fetch_assoc()) {
              $tree[] = $row;
          }
      }
      // ngắt kết nối
      mysqli_close($conn);

      $response = array();
      foreach ($course_nodes as $node) {
         $temp = countAll($node['id'], $tree);
         // echo '<pre>'; print_r($temp);
         $response = mergeResult($response, $temp);
      }
      // die;
      arsort($response);
      $ret = array(
         'root' => 0,
         'relate' => array()
      );

      $root_quantity = 0;
      $root_id = 0;
      $i = 0;
      foreach ($response as $course => $value) {
         $i++;
         if ($i > 1) {
            if ($value > $minSup && ($value/$root_quantity*100) >= $minCon) {
               if (isCov($root_id, $course, $value/$root_quantity)) {
                  $ret['relate'][$course] = $value;
               }
            } else {
               break;
            }
         } else {
            $root_quantity = $value;
            $root_id = $course;
            $ret['root'] = $value;
         }

      }

      return $ret;
   }

   if (isset($_GET['id'])) {
      $id = $_GET['id'];
      // Kết nối CSDL
      $conn = mysqli_connect('localhost', 'root', 'code', 'courses_crm');
      mysqli_set_charset($conn,"utf8");

      // Kiểm tra kết nối
      if ($conn->connect_error) {
          die("Kết nối thất bại: " . $conn->connect_error);
      }

      if (!isset($_GET['action'])) {
         // Get minSup, minCon
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

      	$sql = "SELECT * FROM contact WHERE id = $id";

      	$result = $conn->query($sql);

      	// Kiểm tra số lượng record trả về có lơn hơn 0
      	// Nếu lớn hơn tức là có kết quả, ngược lại sẽ không có kết quả
      	$contacts = array();
      	if ($result->num_rows > 0)
      	{
      	    // Sử dụng vòng lặp while để lặp kết quả
      	    $info = $result->fetch_assoc();
      	}
      	else {
      	    echo "Không có record nào";
      	}

         // get courses
      	// Câu SQL lấy danh sách
      	$sql = "SELECT * FROM tbl_courses ORDER BY code ASC";

      	// Thực thi câu truy vấn và gán vào $result
      	$result = $conn->query($sql);

      	// Kiểm tra số lượng record trả về có lơn hơn 0
      	// Nếu lớn hơn tức là có kết quả, ngược lại sẽ không có kết quả
      	$courses = array();
      	if ($result->num_rows > 0)
      	{
      	    // Sử dụng vòng lặp while để lặp kết quả
      	    while($row = $result->fetch_assoc()) {
      	        $courses[] = $row;
      	    }
      	}
      	else {
      	    echo "Không có record nào";
      	}

         // ngắt kết nối
      	$conn->close();

         $course_rgt = NULL;
         foreach ($courses as $course) {
            if ($info['course_id'] == $course['id']) {
               $course_rgt = $course;
               break;
            }
         }

         $rs = getAssociation($course_rgt['code'], $minSup, $minCon);
      } else {
         // Lệnh update
         $sql = "UPDATE contact SET done=1 WHERE id=$id";

         // Thực hiện update
         if (!mysqli_query($conn, $sql)) {
             echo "Update thất bại: " . mysqli_error($conn);
         }

         $conn->close();
         header('Location: index.php');
      }
   }

   if (isset($_POST['submit'])) {
      $id_contact = $_POST['id'];
      $name = $_POST['name'];
      $list_course = $_POST['courses'];

      $course_arr = explode(",", $list_course);

      // Kết nối CSDL
      $conn = mysqli_connect('localhost', 'root', 'code', 'courses_crm');
      mysqli_set_charset($conn,"utf8");

      // Kiểm tra kết nối
      if ($conn->connect_error) {
          die("Kết nối thất bại: " . $conn->connect_error);
      }

      // UPDATE contact
      $sql = "UPDATE contact SET done=1 WHERE id=$id";

      // Thực hiện update
      if (!mysqli_query($conn, $sql)) {
          echo "Update thất bại: " . mysqli_error($conn);
          die;
      }

      // ADD STUDENT
      $sql = "INSERT INTO tbl_students(name)
              VALUES ('$name')";

      // Thực hiện thêm record
      if (!mysqli_query($conn, $sql)) {
          echo "Lỗi: " . $sql . "<br>" . mysqli_error($conn);
          die;
      }

      $last_id = mysqli_insert_id($conn);

      foreach ($course_arr as $key => $value) {
         $sql = "INSERT INTO tbl_student_courses(student_id, course_id)
                 VALUES ($last_id, $value)";

         // Thực hiện thêm record
         if (!mysqli_query($conn, $sql)) {
             echo "Lỗi: " . $sql . "<br>" . mysqli_error($conn);
             die;
         }
      }

      $conn->close();
      header('Location: index.php');
   }
 ?>


 <!DOCTYPE html>
 <html lang="en">
     <head>
         <meta charset="utf-8">
         <meta http-equiv="X-UA-Compatible" content="IE=edge">
         <meta name="viewport" content="width=device-width, initial-scale=1">
         <meta name="description" content="">
         <meta name="author" content="">

         <title>Hệ thống gợi ý tư vấn khóa học</title>

         <!-- Bootstrap Core CSS -->
         <link href="css/bootstrap.min.css" rel="stylesheet">

         <!-- MetisMenu CSS -->
         <link href="css/metisMenu.min.css" rel="stylesheet">

         <!-- Custom CSS -->
         <link href="css/startmin.css" rel="stylesheet">

         <!-- Morris Charts CSS -->
         <link href="css/morris.css" rel="stylesheet">

         <!-- Custom Fonts -->
         <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">

         <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
         <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
         <!--[if lt IE 9]>
         <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
         <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
         <![endif]-->
 		  <style media="screen">
 		  		.bold {
 		  			font-weight: bold;
 		  		}

             .choose-course {
                 width: 200px;
                 margin-right: 20px;
                 margin-top: 5px;
                 background-color: beige;
             }

             .tag {
                background-color: beige;
                 padding: 10px;
                 border-radius: 10px;
                 color: darkblue;
                 cursor: pointer;
                 margin: 5px;
                 display: inline-block;
             }
 		  </style>
     </head>
     <body>

         <div id="wrapper">

             <!-- Navigation -->
             <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
                 <div class="navbar-header">
                     <a class="navbar-brand" href="index.html">Startmin</a>
                 </div>

                 <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                     <span class="sr-only">Toggle navigation</span>
                     <span class="icon-bar"></span>
                     <span class="icon-bar"></span>
                     <span class="icon-bar"></span>
                 </button>

                 <ul class="nav navbar-nav navbar-left navbar-top-links">
                     <li><a href="#"><i class="fa fa-home fa-fw"></i> Website</a></li>
                 </ul>

                 <ul class="nav navbar-right navbar-top-links">
                     <li class="dropdown navbar-inverse">
                         <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                             <i class="fa fa-bell fa-fw"></i> <b class="caret"></b>
                         </a>
                         <ul class="dropdown-menu dropdown-alerts">
                             <li>
                                 <a href="#">
                                     <div>
                                         <i class="fa fa-comment fa-fw"></i> New Comment
                                         <span class="pull-right text-muted small">4 minutes ago</span>
                                     </div>
                                 </a>
                             </li>
                             <li>
                                 <a href="#">
                                     <div>
                                         <i class="fa fa-twitter fa-fw"></i> 3 New Followers
                                         <span class="pull-right text-muted small">12 minutes ago</span>
                                     </div>
                                 </a>
                             </li>
                             <li>
                                 <a href="#">
                                     <div>
                                         <i class="fa fa-envelope fa-fw"></i> Message Sent
                                         <span class="pull-right text-muted small">4 minutes ago</span>
                                     </div>
                                 </a>
                             </li>
                             <li>
                                 <a href="#">
                                     <div>
                                         <i class="fa fa-tasks fa-fw"></i> New Task
                                         <span class="pull-right text-muted small">4 minutes ago</span>
                                     </div>
                                 </a>
                             </li>
                             <li>
                                 <a href="#">
                                     <div>
                                         <i class="fa fa-upload fa-fw"></i> Server Rebooted
                                         <span class="pull-right text-muted small">4 minutes ago</span>
                                     </div>
                                 </a>
                             </li>
                             <li class="divider"></li>
                             <li>
                                 <a class="text-center" href="#">
                                     <strong>See All Alerts</strong>
                                     <i class="fa fa-angle-right"></i>
                                 </a>
                             </li>
                         </ul>
                     </li>
                     <li class="dropdown">
                         <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                             <i class="fa fa-user fa-fw"></i> secondtruth <b class="caret"></b>
                         </a>
                         <ul class="dropdown-menu dropdown-user">
                             <li><a href="#"><i class="fa fa-user fa-fw"></i> User Profile</a>
                             </li>
                             <li><a href="#"><i class="fa fa-gear fa-fw"></i> Settings</a>
                             </li>
                             <li class="divider"></li>
                             <li><a href="login.html"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                             </li>
                         </ul>
                     </li>
                 </ul>
                 <!-- /.navbar-top-links -->

                 <div class="navbar-default sidebar" role="navigation">
                     <div class="sidebar-nav navbar-collapse">
                         <ul class="nav" id="side-menu">
                             <li class="sidebar-search">
                                 <div class="input-group custom-search-form">
                                     <input type="text" class="form-control" placeholder="Search...">
                                     <span class="input-group-btn">
                                         <button class="btn btn-primary" type="button">
                                             <i class="fa fa-search"></i>
                                         </button>
                                 </span>
                                 </div>
                                 <!-- /input-group -->
                             </li>
                             <li>
                                <a href="register.php"><i class="fa fa-dashboard fa-fw"></i> Đăng ký</a>
                            </li>
									 <li>
                                <a href="buildTree.php"><i class="fa fa-dashboard fa-fw"></i> Xây dựng cây FP</a>
                            </li>
                             <li>
                                 <a href="#"><i class="fa fa-bar-chart-o fa-fw"></i> Charts<span class="fa arrow"></span></a>
                                 <ul class="nav nav-second-level">
                                     <li>
                                         <a href="flot.html">Flot Charts</a>
                                     </li>
                                     <li>
                                         <a href="morris.html">Morris.js Charts</a>
                                     </li>
                                 </ul>
                                 <!-- /.nav-second-level -->
                             </li>
                             <li>
                                 <a href="tables.html"><i class="fa fa-table fa-fw"></i> Tables</a>
                             </li>
                             <li>
                                 <a href="forms.html"><i class="fa fa-edit fa-fw"></i> Forms</a>
                             </li>
                             <li>
                                 <a href="#"><i class="fa fa-wrench fa-fw"></i> UI Elements<span class="fa arrow"></span></a>
                                 <ul class="nav nav-second-level">
                                     <li>
                                         <a href="panels-wells.html">Panels and Wells</a>
                                     </li>
                                     <li>
                                         <a href="buttons.html">Buttons</a>
                                     </li>
                                     <li>
                                         <a href="notifications.html">Notifications</a>
                                     </li>
                                     <li>
                                         <a href="typography.html">Typography</a>
                                     </li>
                                     <li>
                                         <a href="icons.html"> Icons</a>
                                     </li>
                                     <li>
                                         <a href="grid.html">Grid</a>
                                     </li>
                                 </ul>
                                 <!-- /.nav-second-level -->
                             </li>
                             <li>
                                 <a href="#"><i class="fa fa-sitemap fa-fw"></i> Multi-Level Dropdown<span class="fa arrow"></span></a>
                                 <ul class="nav nav-second-level">
                                     <li>
                                         <a href="#">Second Level Item</a>
                                     </li>
                                     <li>
                                         <a href="#">Second Level Item</a>
                                     </li>
                                     <li>
                                         <a href="#">Third Level <span class="fa arrow"></span></a>
                                         <ul class="nav nav-third-level">
                                             <li>
                                                 <a href="#">Third Level Item</a>
                                             </li>
                                             <li>
                                                 <a href="#">Third Level Item</a>
                                             </li>
                                             <li>
                                                 <a href="#">Third Level Item</a>
                                             </li>
                                             <li>
                                                 <a href="#">Third Level Item</a>
                                             </li>
                                         </ul>
                                         <!-- /.nav-third-level -->
                                     </li>
                                 </ul>
                                 <!-- /.nav-second-level -->
                             </li>
                             <li>
                                 <a href="#"><i class="fa fa-files-o fa-fw"></i> Sample Pages<span class="fa arrow"></span></a>
                                 <ul class="nav nav-second-level">
                                     <li>
                                         <a href="blank.html">Blank Page</a>
                                     </li>
                                     <li>
                                         <a href="login.html">Login Page</a>
                                     </li>
                                 </ul>
                                 <!-- /.nav-second-level -->
                             </li>
                         </ul>
                     </div>
                     <!-- /.sidebar-collapse -->
                 </div>
                 <!-- /.navbar-static-side -->
             </nav>

             <div id="page-wrapper">
                 <div class="row">
                     <div class="col-lg-12">
                         <h1 class="page-header">Tư vấn đơn hàng:</h1>
                     </div>
                     <!-- /.col-lg-12 -->
                 </div>
                 <!-- /.row -->
                 <div class="row">
 						 <div class="col-md-7">
 							  <div class="panel panel-default">
 									<div class="panel-heading">
 										 Các khóa học liên quan
 									</div>
 									<!-- /.panel-heading -->
 									<div class="panel-body">
 										 <div class="flot-chart">
 											  <div id="morris-bar-chart"></div>
 										 </div>
 									</div>
 									<!-- /.panel-body -->
 							  </div>
 							  <!-- /.panel -->
 						 </div>
                    <div class="col-md-5">
                       <p>Khách hàng: <span class="bold"><?php echo $info['name'] ?></span></p>
                       <p>Số điện thoại: <span class="bold"><?php echo $info['phone'] ?></span></p>
                       <p>Khóa học lựa chọn: <span class="bold"><?php echo $course_rgt['code'] . ' : ' . $course_rgt['name']; ?></span></p>
                       <div class="panel panel-default">
                           <div class="panel-heading">
                               Trốt khóa học
                           </div>
                           <!-- /.panel-heading -->
                           <div class="panel-body" id="panel_content">
                               <span class="tag" data-id="<?php echo $course_rgt['id']; ?>"><?php echo $course_rgt['code']; ?></span>
                           </div>
                           <div class="panel-footer">
                              <button type="button" name="button" class="btn btn-default pull-right" id="add_course">+</button>
                              <select class="pull-right choose-course" id="course_choose">
                                 <option value="0" class="hidden">(Lựa chọn)</option>
                                 <?php $val = ''; ?>
                                 <?php foreach ($courses as $course): ?>
                                    <?php $val .= $course['id'] . ':' . $course['code'] . ','; ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo $course['code'] . ' : ' . $course['name']; ?></option>
                                 <?php endforeach; ?>
                              </select>
                              <div class="clearfix"></div>
                           </div>
                       </div>
                       <form class="" method="post" style="float: right;">
                          <input type="hidden" id="course_codes" value="<?php echo $val; ?>">
                          <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
                          <input type="hidden" name="name" value="<?php echo $info['name']; ?>">
                          <input type="hidden" name="courses" id="course_list" value="<?php echo $course_rgt['id']; ?>">
                          <input type="submit" name="submit" value="Đồng ý" class="btn btn-success">
                          <a href="process.php?id=<?php echo $info['id']; ?>&action=0" class="btn btn-danger">Từ chối</a>
                          <a href="index.php">Quay lai</a>
                       </form>
                    </div>
                 </div>
                 <!-- /.row -->
             </div>
             <!-- /#page-wrapper -->
             <!-- <textarea name="name" rows="8" cols="80"><?php print_r($rs); ?></textarea> -->
         </div>
         <!-- /#wrapper -->

         <!-- jQuery -->
         <script src="js/jquery.min.js"></script>

         <!-- Bootstrap Core JavaScript -->
         <script src="js/bootstrap.min.js"></script>

         <!-- Metis Menu Plugin JavaScript -->
         <script src="js/metisMenu.min.js"></script>

         <!-- Flot Charts JavaScript -->
         <!-- Morris Charts JavaScript -->
        <script src="js/raphael.min.js"></script>
        <script src="js/morris.min.js"></script>
        <?php if (count($rs['relate']) > 0): ?>
        <script type="text/javascript">
          $(function() {
             Morris.Bar({
                    element: 'morris-bar-chart',
                    data: [
                       <?php foreach ($rs['relate'] as $course_code => $value): ?>
                          {
                             y: '<?php echo $course_code; ?>',
                             a: <?php echo $rs['root']; ?>,
                             b: <?php echo $value; ?>
                         },
                       <?php endforeach; ?>
                    ],
                    xkey: 'y',
                    ykeys: ['a', 'b'],
                    labels: ['Khóa gốc', 'Khóa liên quan'],
                    hideHover: 'auto',
                    resize: true
                });

            });
       </script>
       <?php endif; ?>

 		  <script type="text/javascript">
            $('#add_course').click(function(){
               var course_id = $('#course_choose').val();
               var list_course = $('#course_list').val().split(',');

               list_course.push(course_id);
               $('#course_list').val(list_course.join(','));

               var list_code = $('#course_codes').val().split(',');
               var code = '';

               for (var i = 0; i < list_code.length; i++) {
                  var temp = list_code[i].split(':');
                  if (temp[0] == course_id) {
                     code = temp[1];
                     break;
                  }
               }

               var html = '<span class="tag" data-id="' + course_id + '">' + code + '</span>';
               $('#panel_content').append(html);
            });

            $(document).on("click", ".tag", function(){
               var id_del = $(this).data('id');

               var list_course = $('#course_list').val().split(',');
               var index = list_course.indexOf(id_del.toString());
               if (index > -1) {
                 list_course.splice(index, 1);
               }
               $('#course_list').val(list_course.join(','));

               $(this).fadeOut();
            });
 		  </script>

         <!-- Custom Theme JavaScript -->
         <script src="js/startmin.js"></script>

     </body>
 </html>
