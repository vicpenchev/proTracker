function generatePDF() {
    let canvas = document.querySelector(".fi-main");
    let screenWidth = parseFloat(window.getComputedStyle(canvas).width);

    //let elements = document.querySelectorAll(".fi-page-header-widgets, .fi-ta");
    let elements = document.querySelectorAll(".fi-wi-widget, .fi-ta");
    let images = [];

    elements.forEach(function (el) {
        html2canvas(el, {
            scale: 2480 / screenWidth // 2480px - size for A4 paper, 300 dpi
        }).then(function (canvas) {
            let img = new Image();
            img.src = canvas.toDataURL("image/jpeg");
            images.push(img);

            if (images.length === elements.length) {
                generatePdfWithImages(images);
            }
        });
    });
}

function generatePdfWithImages(images) {
    window.jsPDF = window.jspdf.jsPDF;
    let doc = new jsPDF({orientation: "portrait", unit: "mm", format: "a4"});

    for (let i = 0; i < images.length; i++) {
        let img = images[i];
        let width = (img.width ? img.width : 2300);
        let height = (img.height ? img.height : 5500);
        let aspectRatio = width / height;
        let newWidth = doc.internal.pageSize.width - 10; // subtracting 20 to leave some margin
        let newHeight = newWidth / aspectRatio;
        if (i>0){
            doc.addPage(); // Add a new page before adding the image
        }
        doc.addImage(img, 'JPEG', 5, 5, newWidth, newHeight); // Add the image to the new page
    }

    const date = new Date();
    let day = date.getDate();
    let month = date.getMonth() + 1;
    let year = date.getFullYear();
    let currentDate = day + '-' + month + '-' + year;
    doc.save('Transactions-' + currentDate + '.pdf');
}
