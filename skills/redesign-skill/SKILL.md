---
name: redesign-existing-projects
description: Audit và redesign UI dev/SaaS/dashboard — 4 mode Refresh/Refactor/Rebrand/Rebuild. Không phá chức năng, route, field.
---

# DMCTN Redesign Skill (v0.3)

Dùng khi dự án **đã có UI** (SaaS, dashboard, devtool, docs, agent workspace) và cần nâng cấp.

**Luật:** Không code ngay. Audit + chọn mode + output bắt buộc trước.

Tham chiếu: `skills/taste-skill/SKILL.md` (preset **Redesign Existing UI**), `.cursor/rules/dmctn-taste-gate.mdc`.

---

## Redesign modes (chọn một)

| Mode | Mô tả | Khi dùng |
|------|--------|----------|
| **Refresh** | Làm mới nhẹ: spacing, color, type, micro-components; **giữ layout chính** | UI ổn structure, chỉ “cũ” |
| **Refactor** | Sửa structure/hierarchy, nhóm lại section; **giữ flow & route** | UX lộn, nhưng feature set ổn |
| **Rebrand** | Đổi visual identity (màu, font, tone); **giữ chức năng & layout shell** | Brand mới, flow giữ |
| **Rebuild** | Dựng lại UI khi bản cũ quá yếu; vẫn **map 1:1 feature** | Legacy không cứu được bằng refresh |

**Không dùng Rebuild** trừ khi user/brief xác nhận rủi ro.

---

## Audit (trước mode)

- Route / screen inventory
- Chức năng đang chạy (không được mất)
- Lỗi thị giác, UX, mobile, a11y, performance
- Điểm **không được phá** (API, field name, deep links, permissions)

---

## Anti-slop (redesign)

Không “redesign” thành:

- generic SaaS hero + 3 cards
- gradient AI + glass toàn trang
- đổi tên menu/route làm user lạc
- bỏ loading/empty/error

---

## Output bắt buộc

```text
CURRENT_UI_INVENTORY:
- (screens / components / flows)

KEEP:
- ...

REMOVE:
- ... (lý do; không xóa chức năng ẩn)

IMPROVE:
- ...

SELECTED_REDESIGN_MODE: Refresh | Refactor | Rebrand | Rebuild
RISK_LIST:
- (regression, training cost, a11y break, perf)

IMPLEMENTATION_PLAN:
- Phase 1: ...
- Phase 2: ...

BEFORE_AFTER_REVIEW:
- Brief alignment: PASS/FAIL
- Hierarchy delta: ...
- Mobile: PASS/FAIL
- Accessibility: PASS/FAIL

VERDICT: PASS_TO_CODE | NEEDS_MORE_AUDIT
```

Sau implement: chạy `skills/ui-review-skill/SKILL.md` cho Design QA Score.

---

## PASS / FAIL

**PASS** khi: mode phù hợp; keep/improve có lý do; không mất chức năng; hierarchy tốt hơn measurable.

**FAIL** khi: mất route/field/state; chỉ đổi màu; rebuild không có inventory; mobile/a11y tệ hơn trước.

---

## Quy trình

1. Inventory + audit.
2. Chọn mode (mặc định **Refresh** nếu không chắc).
3. Keep / Remove / Improve list.
4. Risk + implementation plan.
5. Taste Gate + Pre-Flight (`taste-skill`).
6. Code tăng dần theo component.
7. Before/after review + UI Review Skill nếu cần.
