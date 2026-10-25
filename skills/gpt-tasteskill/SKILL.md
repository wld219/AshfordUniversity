---
name: gpt-taste
description: Biến thể chặt hơn cho GPT/Codex: ép lập kế hoạch thiết kế, không nhảy thẳng vào code.
---

# GPT Taste Skill VI

Dùng cho GPT, Codex hoặc agent hay code nhanh nhưng dễ tạo UI chung chung.

## Luật cứng

Không được trả code ở bước đầu. Phải trả:

1. Design Read
2. Dials
3. Anti-slop risks
4. Layout plan
5. Component plan
6. PASS/FAIL gate

## Khi code

- Không placeholder như `TODO`, `lorem ipsum`, `coming soon` nếu user yêu cầu hoàn chỉnh.
- Không xoá tính năng cũ khi redesign.
- Không thêm thư viện nếu chưa kiểm tra package/project.
- Không dùng hardcoded fake data trừ demo được yêu cầu.

## Khi tự kiểm tra

- Soi UI 3 giây: có bản sắc không?
- Soi mobile: chạm được không?
- Soi text: tiếng Việt có dấu, dễ hiểu không?
- Soi trạng thái: loading/empty/error có không?
- Soi build: test/build có PASS không?

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
