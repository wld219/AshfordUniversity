# Quy trình dùng với Cursor

1. Copy `.cursor/rules/dmctn-taste-gate.mdc` vào root dự án.
2. Mở Cursor, bật rule khi làm UI.
3. Dùng prompt trong `examples/prompt-cursor.md`.
4. Bắt Cursor tạo `Design Read` trước khi code.
5. Nếu Cursor nhảy vào code ngay, dừng và yêu cầu chạy Taste Gate.
6. Sau khi code, bắt buộc chạy test/build/verify.

## Gợi ý cho dự án của Tĩnh

- App nội bộ: ưu tiên rõ, nhanh, ít hiệu ứng.
- Web public: thêm SEO gate.
- Dashboard/license: thêm security gate.
- Mobile/PWA/ngrok: kiểm tra màn hình nhỏ trước.
