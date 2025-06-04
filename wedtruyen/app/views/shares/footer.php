<?php
// ...có thể có code PHP ở đây...
?>
<!-- Đặt HTML và CSS sau khi đã đóng PHP -->
<style>
    .footer-main {
        background: linear-gradient(90deg, #232526 0%, #414345 100%);
        color: #fff;
        padding: 32px 0 18px 0;
        text-align: center;
        border-radius: 18px 18px 0 0;
        margin: 40px 16px 0 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.12);
        font-size: 15px;
    }
    .footer-main .footer-contact {
        margin-bottom: 12px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 24px;
        font-size: 15px;
    }
    .footer-main .footer-contact span {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .footer-main .footer-contact i {
        color: #ffd700;
        font-size: 17px;
    }
    .footer-main .footer-copyright {
        font-size: 14px;
        color: #e0e0e0;
        margin-top: 10px;
    }
    @media (max-width: 600px) {
        .footer-main {
            font-size: 13px;
            padding: 20px 0 10px 0;
            margin: 20px 4px 0 4px;
            border-radius: 12px 12px 0 0;
        }
        .footer-main .footer-contact {
            flex-direction: column;
            gap: 8px;
        }
    }
</style>

<footer class="footer-main">
    <div class="footer-contact">
        <span><i class="fas fa-envelope"></i> thinhnguyenkk07@gmail.com</span>
        <span><i class="fas fa-map-marker-alt"></i> 54c đường số 4, P.Trường Thọ, Tp.Thủ Đức, Tp.HCM</span>
        <span><i class="fas fa-phone"></i> 0925148686</span>
    </div>
    <div class="footer-copyright">
        &copy; 2025 Web Đọc. All rights reserved.
    </div>
</footer>