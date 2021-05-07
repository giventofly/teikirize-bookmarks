    <!-- this is a local shop for local people, nothing to see in here -->
    <footer>
    
    </footer>

    <!-- custom css -->
    <?php foreach ($css as $url) {
        echo '<link rel="stylesheet" href="'.get_base_url().'/assets/css/'.$url.'">';
      }
    ?>
    <!-- custom js -->
    <?php foreach ($js as $url) {
        echo '<script src="'.get_base_url().'/assets/js/'.$url.'"></script>';
      }
    ?>
    <!-- google font -->
    <!--
      -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400;700;900&display=swap" rel="stylesheet"> 
  </body>
</html>