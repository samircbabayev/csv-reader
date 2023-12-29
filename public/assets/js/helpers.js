const getBase64 = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });
}

const notify = (icon,message) => {
  Swal.fire({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 5000,
    icon: icon,
    title: message,
    showCloseButton: true,
  });
};


const isNumeric = (value) => {
    return !isNaN(parseFloat(value)) && isFinite(value);
}
