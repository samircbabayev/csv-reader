<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV-READER</title>
    <link rel="stylesheet" href="/assets/css/libs/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/css/master.css">

</head>
<body>
  <section class="first" >
    <div class="container">
      <div class="fist__inner">
        <div class="block-1">
          <h3>Upload</h3>
          <div class="block-1-box">
            <input type="file" name="file_input" id="file_input" style="display: none;">
            <label for="file_input" id="browse_files_button">Browse file</label>
            <span id="selected_file_name"></span>
            <div class="drag-drop-area">
              <p>Click the file & drag or drop it here (drag & drop)</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <script type="text/javascript" src="/assets/js/lib/jquery-3.6.4.min.js"></script>
  <script type="text/javascript" src="/assets/js/lib/sweetalert2.min.js"></script>
  <script type="text/javascript" src="/assets/js/helpers.js"></script>

  <script type="text/javascript" src="/assets/js/downloadFile.js"></script>
  <script type="text/javascript" src="/assets/js/master.js"></script>
</body>
</html>
