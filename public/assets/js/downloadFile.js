$(function() {
  const fileInput = $('#file_input');
  const browseFilesButton = $('#browse_files_button');
  const dragDropArea = $('.drag-drop-area');
  const selectedFileName = $('#selected_file_name');

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
    $(this).val('');
  }

  function handleDroppedFiles(files) {
    if (files.length > 0) {
      const file = files[0];
      // console.log(file);
      installFile(file);
      selectedFileName.text(`Selected file: ${file.name}`);
    }
  }

  function installFile(file) {
    getBase64(file).then((result) => {
      const file_base64 = result;

      $.ajax({
        url: `/?route=download-products`,
        method: `POST`,
        data: {file_base64, file_name: file.name},
        success: function(d) {
          if(d.code === 200) {
            Swal.fire(d.message, '', 'success');
          } else {
            Swal.fire(d.message, '', 'warning');
          }
        },
        error: function(d) {
          Swal.fire(d.message, '', 'warning');
        }
      });

    },function() {}).catch(e => {
      console.log(e);
    });
  }
});
