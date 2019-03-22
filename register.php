<?php
   // Kết nối CSDL
   $conn = mysqli_connect('localhost', 'root', 'code', 'courses_crm');
   mysqli_set_charset($conn,"utf8");

   // Kiểm tra kết nối
   if ($conn->connect_error) {
       die("Kết nối thất bại: " . $conn->connect_error);
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
      die;
   }

   if (isset($_POST['submit'])) {
      $name = $_POST['name'];
      $phone = $_POST['phone'];
      $course = $_POST['course'];

      // Kết nối CSDL
      $conn = mysqli_connect('localhost', 'root', 'code', 'courses_crm');
      mysqli_set_charset($conn,"utf8");

      // Kiểm tra kết nối
      if ($conn->connect_error) {
          die("Kết nối thất bại: " . $conn->connect_error);
      }

      // ADD CONTACT
      $sql = "INSERT INTO contact(name, phone, course_id)
              VALUES ('$name', '$phone', $course)";

      // Thực hiện thêm record
      if (!mysqli_query($conn, $sql)) {
          echo "Lỗi: " . $sql . "<br>" . mysqli_error($conn);
          die;
      }

      $conn->close();
      header('Location: index.php');
   }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
   <head>
      <meta charset="utf-8">
      <title>Đăng ký mua khóa học</title>
   </head>
   <body>
      <form method="post" style="width: 650px;
    margin: auto;
    background-color: antiquewhite;
    padding: 40px;
    border-radius: 10px;">
         <h2 style="text-align: center;">Đăng ký khóa học tại Lakita</h2>
         <label for="name">Họ và tên</label>
         <input id="name" type="text" name="name" value="" style="width: 250px;">
         <label for="name">SDT</label>
         <input id="phone" type="text" name="phone" value="" style="width: 250px;">
         <label for="course">Khóa học muốn mua</label>
         <select id="course"  name="course" style="width: 280px;
    margin-top: 6px;">
            <option value="0" style="display: hidden;">(Lựa chọn)</option>
            <?php foreach ($courses as $course): ?>
               <option value="<?php echo $course['id'] ?>"><?php echo $course['code'] . ' : ' . $course['name']; ?></option>
            <?php endforeach; ?>
         </select>
         <input type="submit" name="submit" value="OK" style="width: 100px;
    background-color: darkorange;">
      </form>
      <a href="register.php">Dang ky</a>
      <a href="index.php">Cham soc</a>
      <a href="buildTree.php">Dung cay FPGrowth</a>
   </body>
</html>
