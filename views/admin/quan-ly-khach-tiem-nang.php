<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales CRM | Monkey Gym Admin</title>
    <link rel="icon" type="image/png" href="<?= ASSET_URL ?>/favicon.png?v=2">
    <link rel="stylesheet" href="<?= ASSET_URL ?>/css/styles.css">
    <!-- Nạp SortableJS qua CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        .crm-container {
            padding: 30px;
        }

        .crm-header {
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .crm-header h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .crm-header p {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* CRM Board */
        .crm-board {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding-bottom: 20px;
            align-items: flex-start;
        }

        .crm-column {
            flex: 0 0 300px;
            background: #f8fafc;
            border: 1px solid var(--border-light);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            max-height: 80vh;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }

        .crm-column-header {
            padding: 20px;
            background: white;
            border-bottom: 1px solid var(--border-light);
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .crm-column-header h3 {
            font-size: 0.9rem;
            font-weight: 800;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-primary);
        }

        .crm-column-header .count {
            font-size: 0.75rem;
            background: var(--gold-bg);
            color: var(--gold-dark);
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 700;
        }

        .crm-items {
            flex: 1;
            padding: 12px;
            overflow-y: auto;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Lead Card */
        .lead-card {
            background: white;
            padding: 16px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            cursor: grab;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            position: relative;
        }

        .lead-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            border-color: var(--gold);
        }

        .lead-card:active {
            cursor: grabbing;
        }

        .lead-card.dragging {
            opacity: 0.4;
            transform: scale(0.95);
        }

        .lead-name {
            font-weight: 800;
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: block;
        }

        .lead-info {
            font-size: 0.85rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .lead-date {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed var(--border-light);
            display: flex;
            justify-content: space-between;
        }

        /* Status Colors */
        .column-chua_goi .crm-column-header { border-top: 4px solid #94a3b8; }
        .column-da_goi .crm-column-header { border-top: 4px solid #3b82f6; }
        .column-dang_cho .crm-column-header { border-top: 4px solid #f59e0b; }
        .column-da_chot .crm-column-header { border-top: 4px solid #10b981; }
        .column-that_bai .crm-column-header { border-top: 4px solid #ef4444; }

        /* Custom Scrollbar */
        .crm-items::-webkit-scrollbar { width: 6px; }
        .crm-items::-webkit-scrollbar-track { background: transparent; }
        .crm-items::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php require __DIR__ . '/layout/thanh-ben.php'; ?>
        
        <div class="admin-content">
            <div class="crm-container">
                <div class="crm-header">
                    <div>
                        <h1>💼 Sales CRM - Phễu Khách Hàng</h1>
                        <p>Quản lý toàn bộ quy trình chăm sóc khách hàng đăng ký tập thử từ hệ thống.</p>
                    </div>
                    <div style="background: white; padding: 12px 20px; border-radius: 12px; border: 1px solid var(--border-light); text-align: right;">
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Tổng tiềm năng</div>
                        <div style="font-size: 1.5rem; font-weight: 900; color: var(--gold-dark);"><?= array_sum(array_map('count', $board)) ?> <span style="font-size: 0.9rem;">khách</span></div>
                    </div>
                </div>

                <div class="crm-board">
                    <?php 
                    $columns = [
                        'chua_goi' => ['title' => '👤 Khách Mới', 'icon' => '👤'],
                        'da_goi'   => ['title' => '☎️ Đã Gọi', 'icon' => '☎️'],
                        'dang_cho' => ['title' => '👁️ Theo Dõi', 'icon' => '👁️'],
                        'da_chot'  => ['title' => '✅ Chốt Đơn', 'icon' => '✅'],
                        'that_bai' => ['title' => '❌ Thất Bại', 'icon' => '❌']
                    ];
                    
                    foreach ($columns as $status => $cfg): 
                    ?>
                    <div class="crm-column column-<?= $status ?>">
                        <div class="crm-column-header">
                            <h3><?= $cfg['title'] ?></h3>
                            <span class="count"><?= count($board[$status]) ?></span>
                        </div>
                        <div class="crm-items" data-status="<?= $status ?>">
                            <?php foreach($board[$status] as $lead): ?>
                            <div class="lead-card" data-id="<?= $lead['id'] ?>">
                                <span class="lead-name"><?= htmlspecialchars($lead['ho_ten']) ?></span>
                                <div class="lead-info">
                                    <span>📞</span> <?= htmlspecialchars($lead['so_dien_thoai']) ?>
                                </div>
                                <?php if (!empty($lead['email'])): ?>
                                <div class="lead-info">
                                    <span>📧</span> <?= htmlspecialchars($lead['email']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="lead-date">
                                    <span>🕒 <?= date('d/m/Y', strtotime($lead['ngay_dang_ky'])) ?></span>
                                    <span style="opacity: 0.6;"><?= date('H:i', strtotime($lead['ngay_dang_ky'])) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Khởi tạo Sortable cho tất cả các cột
        document.querySelectorAll('.crm-items').forEach(column => {
            new Sortable(column, {
                group: 'leads',
                animation: 200,
                ghostClass: 'dragging',
                onEnd: function (evt) {
                    const item = evt.item;
                    const newStatus = evt.to.dataset.status;
                    const leadId = item.dataset.id;
                    
                    // Cập nhật lên server qua API
                    updateStatus(leadId, newStatus);
                    
                    // Cập nhật số lượng hiển thị trên tiêu đề cột
                    updateCounts();
                }
            });
        });

        function updateStatus(id, status) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', status);

            fetch('<?= SITE_URL ?>/api/admin/update-lead-status', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    console.error('Lỗi khi cập nhật trạng thái:', data.message);
                }
            })
            .catch(err => console.error('Lỗi kết nối API:', err));
        }

        function updateCounts() {
            document.querySelectorAll('.crm-column').forEach(col => {
                const count = col.querySelector('.crm-items').children.length;
                col.querySelector('.count').textContent = count;
            });
        }
    </script>
</body>
</html>
