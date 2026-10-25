---
name: full-output-enforcement
description: Ép agent xuất kết quả đầy đủ, không bỏ dở, không placeholder, có test và verdict.
---

# Output Skill VI

Dùng để chống agent làm nửa chừng.

## Không được

- “phần còn lại tương tự”
- TODO placeholder
- bỏ file quan trọng
- không chạy test
- báo PASS khi chưa kiểm chứng
- nói đã làm nhưng không có evidence

## Kết quả bắt buộc

```text
Summary
Files changed
Tests run
PASS/FAIL evidence
Remaining issues
Verdict
```

## Nếu không làm được hết

Phải nói rõ phần đã làm, phần chưa làm, lý do, và bước tiếp theo có thể test được. Không giả PASS.

## Quy trình bắt buộc

1. Đọc brief và inventory hiện có.
2. Xác định người dùng thật, không thiết kế theo gu mơ hồ.
3. Chọn cảm giác thương hiệu trước khi chọn màu/component.
4. Đặt ba biến: `DESIGN_VARIANCE`, `MOTION_INTENSITY`, `VISUAL_DENSITY`.
5. Chỉ dùng hiệu ứng nếu nó giúp người dùng hiểu hoặc thao tác tốt hơn.
6. Thiết kế mobile-first, sau đó mới mở rộng desktop.
7. Trả về PASS/FAIL rõ ràng trước khi code.

## Anti-slop mặc định

Không mặc định dùng:

- gradient tím/xanh kiểu AI SaaS
- hero căn giữa + subtitle + 2 CTA + mockup lơ lửng
- 3 feature card ngang giống nhau
- glassmorphism toàn bộ giao diện
- font Inter/slate mặc định khi không có lý do
- icon/emoji lộn xộn
- animation vô hạn gây phân tâm

## Output trước khi làm

```text
DESIGN_READ: ...
DIALS: variance/motion/density = ...
PLAN: ...
ANTI_SLOP_RISKS: ...
VERDICT: PASS_TO_CODE hoặc NEEDS_BRIEF
```
