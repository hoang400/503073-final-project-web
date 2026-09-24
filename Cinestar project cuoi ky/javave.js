// SV2 - JAVASCRIPT XỬ LÝ LOGIC ĐẶT VÉ & THANH TOÁN
document.addEventListener("DOMContentLoaded", function () {
    const seatContainer = document.getElementById("seatContainer");
    const displaySeats = document.getElementById("displaySeats");
    const totalPriceElement = document.getElementById("totalPrice");
    const btnPay = document.getElementById("btnPay");
    
    // Giá vé định cấu hình
    const PRICE_REGULAR = 90000;
    const PRICE_VIP = 110000;
    const PRICE_SOLO = 79000;
    const PRICE_COUPLE = 109000;

    let selectedSeatsData = []; // Lưu danh sách ghế người dùng chọn

    // 1. TỰ ĐỘNG TẠO 5 HÀNG GHẾ (A, B, C, D, E)
    const rows = ['A', 'B', 'C', 'D', 'E'];
    const seatsPerRow = 10;

    rows.forEach((rowName, rowIndex) => {
        const rowDiv = document.createElement("div");
        rowDiv.classList.add("seat-row");

        for (let i = 1; i <= seatsPerRow; i++) {
            const seat = document.createElement("span");
            seat.classList.add("seat");
            const seatId = `${rowName}${i}`;
            seat.dataset.id = seatId;

            // Phân loại ghế VIP cho hàng C và D
            if (rowName === 'C' || rowName === 'D') {
                seat.classList.add("vip");
                seat.dataset.price = PRICE_VIP;
            } else {
                seat.dataset.price = PRICE_REGULAR;
            }

            // Giả lập một số ghế đã có người đặt sẵn (Occupied)
            if ((rowName === 'A' && i === 3) || (rowName === 'C' && i === 5)) {
                seat.classList.add("occupied");
            }

            // Sự kiện Click chọn ghế
            seat.addEventListener("click", function () {
                if (this.classList.contains("occupied")) return;

                this.classList.toggle("selected");
                
                if (this.classList.contains("selected")) {
                    selectedSeatsData.push({ id: seatId, price: parseInt(this.dataset.price) });
                } else {
                    selectedSeatsData = selectedSeatsData.filter(s => s.id !== seatId);
                }

                calculateTotal();
            });

            rowDiv.appendChild(seat);
        }
        seatContainer.appendChild(rowDiv);
    });

    // 2. LẮP SỰ KIỆN TÍNH TIỀN BẮP NƯỚC
    document.getElementById("comboSolo").addEventListener("input", calculateTotal);
    document.getElementById("comboCouple").addEventListener("input", calculateTotal);

    // 3. HÀM TÍNH TỔNG TIỀN TỰ ĐỘNG
    function calculateTotal() {
        let seatTotal = selectedSeatsData.reduce((sum, seat) => sum + seat.price, 0);
        
        let qtySolo = parseInt(document.getElementById("comboSolo").value) || 0;
        let qtyCouple = parseInt(document.getElementById("comboCouple").value) || 0;
        let comboTotal = (qtySolo * PRICE_SOLO) + (qtyCouple * PRICE_COUPLE);

        let grandTotal = seatTotal + comboTotal;

        // Cập nhật giao diện
        if (selectedSeatsData.length > 0) {
            displaySeats.innerText = selectedSeatsData.map(s => s.id).join(", ");
        } else {
            displaySeats.innerText = "Chưa chọn";
        }

        totalPriceElement.innerText = grandTotal.toLocaleString('vi-VN') + " VNĐ";
        return grandTotal;
    }

    // 4. MỞ MODAL THANH TOÁN MÔ PHỎNG
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));

    btnPay.addEventListener("click", function () {
        if (selectedSeatsData.length === 0) {
            alert("⚠️ Vui lòng chọn ít nhất 1 ghế trước khi bấm thanh toán!");
            return;
        }

        const grandTotal = calculateTotal();
        const seatsText = selectedSeatsData.map(s => s.id).join(", ");

        // Cập nhật thông tin vào Modal
        document.getElementById("modalSeats").innerText = seatsText;
        document.getElementById("modalTotal").innerText = grandTotal.toLocaleString('vi-VN') + " VNĐ";

        // Tạo mã QR linh hoạt dựa theo số tiền
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=CINESTAR_PAYMENT_TOTAL_${grandTotal}`;
        document.getElementById("qrCodeImg").src = qrUrl;

        paymentModal.show();
    });

    // 5. HOÀN TẤT ĐẶT VÉ (LƯU VÀO LOCALSTORAGE)
    document.getElementById("btnFinish").addEventListener("click", function () {
        const orderInfo = {
            movie: "Avengers: Endgame",
            seats: selectedSeatsData.map(s => s.id),
            total: calculateTotal(),
            time: "18:30",
            createdDate: new Date().toLocaleString('vi-VN')
        };

        // Lưu dữ liệu vào bộ nhớ trình duyệt
        localStorage.setItem("userTicket", JSON.stringify(orderInfo));

        alert("🎉 Đặt vé thành công! Cảm ơn bạn đã trải nghiệm.");
        paymentModal.hide();
        window.location.href = "index.html"; // Chuyển về trang chủ
    });
});

