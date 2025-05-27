<?php
// Kết nối database
require_once(__DIR__ . '/../app/config/connect.php');

// Lấy tất cả truyện từ database
$sql = "SELECT id_truyen as id, ten_truyen, anh_bia as hinh_anh FROM truyen ORDER BY RAND() LIMIT 15";
$result = $conn->query($sql);
$novels = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $novels[] = $row;
    }
}

// Chia truyện thành các nhóm 5 truyện
$novel_groups = array_chunk($novels, 5);
?>

<div class="recommended-novels mt-4">
    <h3 class="section-title">Truyện Đề Cử</h3>
    
    <div id="recommendedNovels" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach($novel_groups as $index => $group): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="row">
                        <?php foreach($group as $novel): ?>
                            <div class="col">
                                <div class="novel-card">
                                    <a href="/Wed_Doc_Truyen/wedtruyen/app/views/truyen/chiTietTruyen.php?id_truyen=<?php echo $novel['id']; ?>" class="text-decoration-none">
                                        <img src="/Wed_Doc_Truyen/<?php echo $novel['hinh_anh'] ?: 'assets/images/default-cover.jpg'; ?>" class="card-img-top" alt="<?php echo $novel['ten_truyen']; ?>">
                                        <div class="card-body">
                                            <h5 class="card-title text-center"><?php echo $novel['ten_truyen']; ?></h5>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <button class="carousel-control-prev" type="button" data-bs-target="#recommendedNovels" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#recommendedNovels" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>

<style>
.recommended-novels {
    margin-bottom: 2rem;
}

.section-title {
    margin-bottom: 1.5rem;
    color: #333;
    font-weight: bold;
}

.novel-card {
    transition: transform 0.2s;
    margin-bottom: 1rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.novel-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.novel-card img {
    width: 100%;
    height: 250px;
    object-fit: cover;
}

.novel-card .card-body {
    padding: 0.8rem;
}

.novel-card .card-title {
    font-size: 0.9rem;
    margin-bottom: 0;
    color: #333;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.carousel-control-prev,
.carousel-control-next {
    width: 5%;
    background-color: rgba(0,0,0,0.3);
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    background-color: rgba(0,0,0,0.5);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo carousel với interval 3 giây
    var recommendedCarousel = new bootstrap.Carousel(document.getElementById('recommendedNovels'), {
        interval: 3000
    });
});
</script> 