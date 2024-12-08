# 简单的随机图片API

## 功能

- **自动设备检测**：识别用户是通过移动设备还是 PC 访问。
- **手动类型指定**：通过 `type=pc` 或 `type=mobile` 参数覆盖自动检测。
- **JSON 响应**：使用 `m=json` 参数返回包含图片 URL 和元数据的 JSON 格式响应。

## 前提条件

- **Web 服务器**：Apache、Nginx 等支持 PHP 的服务器。
## 安装

1. **克隆仓库**

   ```bash
   git clone https://github.com/yourusername/device-based-image-responder.git
   ```

2. **进入目录**

   ```bash
   cd device-based-image-responder
   ```

3. **设置图片 URL 文件**

   创建 `pc_image_urls.txt` 和 `mobile_image_urls.txt` 文件，每个文件每行一个图片 URL。

4. **部署脚本**

   将 `index.php` 放置在您的 Web 服务器上希望访问的目录中。

## 配置

- **允许跨域**

  编辑 `index.php` 中的 `Access-Control-Allow-Origin` 头部，设置为您的受信任域名：

  ```php
  header('Access-Control-Allow-Origin: https://yourdomain.com');
  ```

- **允许的 HTTP 方法和头部**

  根据需要调整头部设置：

  ```php
  header('Access-Control-Allow-Methods: GET, POST');
  header('Access-Control-Allow-Headers: Content-Type,*');
  ```

## 使用

### 自动检测

无需参数，脚本将自动检测设备类型并返回相应的图片。

```bash
https://yourdomain.com/index.php
```

### 手动类型指定

通过 `type` 参数指定设备类型，覆盖自动检测。

- **PC 图片**

  ```bash
  https://yourdomain.com/index.php?type=pc
  ```

- **移动设备图片**

  ```bash
  https://yourdomain.com/index.php?type=mobile
  ```

### JSON 响应模式

添加 `m=json` 参数，获取包含图片 URL 和元数据的 JSON 响应。

```bash
https://yourdomain.com/index.php?m=json
```

结合 `type` 参数：

```bash
https://yourdomain.com/index.php?type=mobile&m=json
```

## 错误处理

- **无效的 `type` 参数**：

  - **状态码**：400 Bad Request

  - **响应**：

    ```json
    "error": "Invalid type parameter. Allowed values are \"pc\" and \"mobile\"."
    ```
  
- **图片 URL 文件问题**：

  - **状态码**：500 Internal Server Error

  - **响应**：

    ```json
    "error": "Unable to read the image URLs file."
    ```
  
- **未找到图片 URL**：

  - **状态码**：500 Internal Server Error

  - **响应**：

    ```json
    "error": "No image URLs found in the file."
    ```
  
- **获取图片失败**：

  - **状态码**：500 Internal Server Error

  - **响应**：

    ```json
    "error": "Failed to fetch the image."
    ```
