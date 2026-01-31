# XBoard Stripe Payment Plugin

一个为 [XBoard](https://github.com/cedar2025/Xboard) 开发的 Stripe 支付插件。支持多种支付方式（信用卡、支付宝、微信支付等）及自定义货币配置。

## ✨ 特性

- **多支付方式支持**: 可在后台灵活配置允许的支付方式（如 `card`, `alipay`, `wechat_pay`, `ideal` 等）。
- **自定义货币**: 支持自定义货币单位（如 CNY, USD, HKD 等），自动适配不同地区的业务需求。
- **无需同步商品**: 直接使用 Stripe Checkout 的 Ad-hoc Price 模式，无需在 Stripe 后台手动创建商品，减少维护成本。
- **安全回调**: 使用 Stripe Webhook 签名验证机制，确保交易回调安全可靠。

## 🚀 安装

1. 下载本插件源码。
2. 将 `Stripe` 文件夹上传至 XBoard 站点的 `/plugins/` 目录下。
3. 登录 XBoard 管理后台，在 **插件管理** 中启用该插件。

## ⚙️ 配置说明

在 **支付配置** -> **Stripe** 中填写以下信息：

| 字段                  | 说明                                       | 示例                |
| :-------------------- | :----------------------------------------- | :------------------ |
| **货币单位**          | 交易使用的货币代码 (ISO 4217)              | `cny` 或 `usd`      |
| **Stripe Secret Key** | Stripe 后台获取的 Secret Key (sk_live_...) | `sk_live_xxxxxxxxx` |
| **WebHook 密钥**      | Stripe Webhook 签名验证密钥 (whsec_...)    | `whsec_xxxxxxxxx`   |
| **支付方式**          | 允许使用的支付方式列表，用英文逗号分隔     | `card,alipay`       |

### Webhook 设置指南

1. 登录 Stripe Dashboard -> Developers -> Webhooks。
2. 点击 **Add endpoint**。
3. **Endpoint URL**: 填入 `https://你的域名.com/api/v1/guest/payment/notify/Stripe/YOUR_UUID`。
   - *注意: UUID 需在 XBoard 后台保存支付方式后，从浏览器地址栏获取。*
4. **Events to send**: 选择 `checkout.session.completed`。
5. 保存后，将页面显示的 **Signing secret** (whsec_...) 填入 XBoard 的 **WebHook 密钥** 字段。

## ☕ Support / Donate

如果你觉得这个插件对你有帮助，欢迎请我喝杯咖啡！

<a href="https://www.buymeacoffee.com/markfan" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>
