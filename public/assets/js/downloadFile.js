$(function() {
  // DOM elements
  const fileInput = $('#file_input');
  const browseFilesButton = $('#browse_files_button');
  const dragDropArea = $('.drag-drop-area');
  const selectedFileName = $('#selected_file_name');

  // Event listeners
  fileInput.on('change', handleFileSelection);

  dragDropArea.on('dragover', function (e) {
    e.preventDefault();
    dragDropArea.addClass('drag-over');
  });

  dragDropArea.on('dragleave', function () {
    dragDropArea.removeClass('drag-over');
  });

  dragDropArea.on('drop', function (e) {
    e.preventDefault();
    dragDropArea.removeClass('drag-over');

    const files = e.originalEvent.dataTransfer.files;
    handleDroppedFiles(files);
  });

  // Handle file selection from input
  function handleFileSelection(event) {
    const files = event.target.files;

    if (files.length === 1) {
      const file = files[0];
      installFile(file);
      selectedFileName.text(`Selected file: ${file.name}`);
    } else {
      Swal.fire('Please select only one file.', '', 'warning');
      fileInput.val('');
      selectedFileName.text('');
    }
    // Clear the input to allow selecting the same file again
    $(this).val('');
  }

  // Handle file dropped onto the drag-drop area
  function handleDroppedFiles(files) {
    if (files.length > 0) {
      const file = files[0];
      installFile(file);
      selectedFileName.text(`Selected file: ${file.name}`);
    }
  }

  // Process and install the selected file
  function installFile(file) {
    getBase64(file).then((result) => {
      const file_base64 = result;

      // Send AJAX request to download the products
      $.ajax({
        url: `/?route=download-products`,
        method: `POST`,
        data: {file_base64, file_name: file.name},
        success: function(response) {
          if (response.code === 200) {
            Swal.fire(response.message, '', 'success');
          } else {
            Swal.fire(response.message, '', 'warning');
          }
        },
        error: function(response) {
          Swal.fire(response.message, '', 'warning');
        }
      });

    }, function() {}).catch(e => {
      console.log(e);
    });
  }
});
