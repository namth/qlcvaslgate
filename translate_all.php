<?php
/*
    Template Name: Vietnamese to English Translation Tool
*/

// Initialize variables
$translated_text = '';
$input_text = '';
$thongbao = '';

// Process form submission
if (
    isset($_POST['post_nonce_field']) &&
    wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')
) {
    $input_text = stripslashes($_POST['text_to_translate']);
    
    // Array of translations - Vietnamese => English
$translations = [
    // HTML content translations
    "<p>Đã cập nhật thành công đơn gia hạn này trên hệ thống gia hạn.</p>" => "<p>Successfully updated this renewal application in the renewal system.</p>",
    "<p>Đã tạo đơn gia hạn mới trên hệ thống gia hạn.</p>" => "<p>Successfully created new renewal application in the renewal system.</p>",
    
    // System messages
    "Chọn đúng loại vai trò cho đối tác và tải file excel lên và bấm Import" => "Select the correct role type for partner, upload excel file and click Import",
    "Không thể kết nối tới server." => "Cannot connect to server.",
    "Không tìm thấy dữ liệu." => "No data found.",
    "Không tạo mới/cập nhật được bài viết.<br>" => "Cannot create/update post.<br>",
    "Mật khẩu cũ không đúng hoặc mật khẩu xác nhận không khớp." => "Old password is incorrect or confirmation password doesn't match.",
    "Mỗi email cách nhau dấu \",\"" => "Each email separated by \",\"",
    "Mời bạn nhập mật khẩu." => "Please enter password.",
    "Mời bạn nhập User ID / Email." => "Please enter User ID / Email.",
    
    // Task and workflow management
    "Danh sách nhiệm vụ chờ phê duyệt (task)" => "Pending task list (task)",
    "Danh sách nhiệm vụ đã hoàn thành" => "Completed task list",
    "Danh sách nhiệm vụ đang thực hiện" => "In-progress task list",
    "Phê duyệt nhiệm vụ" => "Approve task",
    "Từ chối nhiệm vụ" => "Reject task",
    "Nhiệm vụ con" => "Sub-task",
    "Người phụ trách" => "Assignee",
    "Người giao việc" => "Task creator",
    "Ngày bắt đầu" => "Start date",
    "Ngày kết thúc" => "End date",
    
    // Templates and UI
    "Danh sách nhân sự (user)" => "Staff list (user)",
    "Danh sách nhân viên" => "Employee list", 
    "Danh sách thông báo (Notification)" => "Notification list (Notification)",
    "Danh sách tài chính của công việc" => "Job financial list",
    "Danh sách đối tác" => "Partner list",
    "Danh sách khách hàng" => "Customer list",
    "Danh sách công việc" => "Job list",
    "Danh sách dự án" => "Project list",
    "Deadline" => "Deadline",
    "Deadline chờ phản hồi" => "Response deadline",
    "deadline chờ phản hồi" => "response deadline",
    "Dữ liệu nào chưa có sẽ được tạo mới, dữ liệu đã có sẽ được cập nhật." => "Missing data will be created, existing data will be updated.",
    
    // Basic fields
    "Email" => "Email",
    "Emails" => "Emails", 
    "Export Excel" => "Export Excel",
    "File ảnh" => "Image file",
    "Ghi chú" => "Notes",
    "Ghi nhớ." => "Remember me.",
    "Giá trị" => "Value",
    "giây" => "seconds",
    "giờ" => "hours",
    "Group" => "Group",
    "Groups" => "Groups",
    "Gửi" => "Send",
    "Gửi email thông báo cho khách" => "Send notification email to customer",
    
    // Status and actions
    "Hoàn thành luôn" => "Complete immediately",
    "Hoàn tất" => "Complete",
    "https://9outfit.com/" => "https://9outfit.com/",
    "Huỷ" => "Cancel",
    "Hành động" => "Action",
    "hãy kiểm tra để thực hiện nhiệm vụ mới." => "please check to perform new task.",
    "hãy kiểm tra để thực hiện." => "please check to perform.",
    "Hướng dẫn cập nhật qua file excel" => "Excel file update guide",
    
    // Personal info
    "Họ" => "Last name",
    "Họ và tên" => "Full name", 
    "Hồ sơ của bạn" => "Your profile",
    "Hồ sơ nhân sự" => "Staff profile",
    "Import partner to system from excel files" => "Import partner to system from excel files",
    "Job" => "Job",
    "Jobs" => "Jobs",
    
    // Time and dates
    "Khoảng thời gian: " => "Time period: ",
    "Khách hàng" => "Customer",
    "Khách hàng trên mỗi quốc gia" => "Customers per country",
    "Khách hàng đã tồn tại trong hệ thống." => "Customer already exists in system.",
    "không còn là người quản lý việc" => "no longer the job manager",
    "không còn là người xử lý việc" => "no longer the job handler",
    "Không có dữ liệu." => "No data.",
    "Không phê duyệt" => "Disapprove",
    "Không phê duyệt nhiệm vụ (task)" => "Disapprove task (task)",
    "Không thể kết nối tới server." => "Cannot connect to server.",
    "Không tìm thấy dữ liệu." => "No data found.",
    "Không tạo mới/cập nhật được bài viết.<br>" => "Cannot create/update post.<br>",
    
    // Work types
    "Kiểu dáng" => "Design",
    "Kết quả công việc" => "Job results", 
    "Kết quả tìm kiếm" => "Search results",
    "Kết quả tìm kiếm cho:" => "Search results for:",
    
    // Links and files
    "Link file hồ sơ" => "Profile file link",
    "Link hồ sơ" => "Profile link",
    "Link tài liệu" => "Document link",
    "Link tới bản mô tả bộ ảnh" => "Link to image set description",
    "Link tới bản mô tả của bộ ảnh" => "Link to image set description", 
    "Link tới bản mô tả sáng chế" => "Link to patent description",
    "Link tới bộ ảnh" => "Link to image set",
    "Link tới công việc:" => "Link to job:",
    "Link tới tài liệu" => "Link to document",
    "List công việc" => "Job list",
    "Login" => "Login",
    "Logo" => "Logo",
    
    // Categories and types
    "Loại" => "Type",
    "Loại dữ liệu" => "Data type",
    "Loại tiền" => "Currency",
    "Loại tiền tệ" => "Currency type",
    "Lý do" => "Reason",
    "Lưu ý" => "Note",
    "Lưu ý công việc" => "Job note",
    "Lưu ý công việc đến hạn " => "Due job note ",
    "Lưu ý: nếu là đầu việc lớn có nhiều nhiệm vụ con thì bỏ qua trường thông tin này." => "Note: if it's a big job with many sub-tasks, skip this information field.",
    "Lần nhắc thứ " => "Reminder ",
    "Lần nhắc thứ 4 đối với đầu việc:" => "4th reminder for job:",
    
    // History and tracking
    "Lịch sử" => "History",
    "Lịch sử CV" => "CV History",
    "Lịch sử công việc" => "Job history", 
    "Lịch sử thực hiện" => "Execution history",
    "Lọc" => "Filter",
    "Lọc theo deadline:" => "Filter by deadline:",
    "Lọc theo:" => "Filter by:",
    
    // Codes and partners
    "Mã code" => "Code",
    "Mã đối tác" => "Partner code",
    "Mẫu file excel" => "Excel file template",
    "Mẫu mail" => "Email template",
    
    // Authentication
    "Mật khẩu" => "Password",
    "Mật khẩu cũ" => "Old password",
    "Mật khẩu cũ không đúng hoặc mật khẩu xác nhận không khớp." => "Old password is incorrect or confirmation password doesn't match.",
    "Mật khẩu mới" => "New password",
    "Mỗi email cách nhau dấu \",\"" => "Each email separated by \",\"",
    "Mời bạn nhập mật khẩu." => "Please enter password.",
    "Mời bạn nhập User ID / Email." => "Please enter User ID / Email.",
    "Nam Tran" => "Nam Tran",
    
    // Sources and origins
    "Nguồn không xác định" => "Unknown source",
    "Nguồn việc" => "Job source",
    "Nguồn đầu việc" => "Job source",
    "ngày" => "days",
    "Ngày cấp bằng: " => "Certificate date: ",
    "Ngày cập nhật" => "Update date",
    "Ngày nộp đơn: " => "Application date: ",
    "Ngày phát sinh nhiệm vụ" => "Task creation date",
    "Ngày thu/chi" => "Income/Expense date",
    "Ngày tháng" => "Date",
    "ngày để trả lời." => "days to respond.",
    
    // People and roles
    "Người nhận phiếu" => "Receipt recipient",
    "Người nhận:" => "Recipient:",
    "Người quản lý" => "Manager",
    "người quản lý từ" => "manager from",
    "Người quản lý:" => "Manager:",
    "Người thực hiện" => "Assignee",
    "người thực hiện từ" => "assignee from", 
    "Người đại diện" => "Representative",
    "Nhiệm vụ" => "Task",
    "nhiệm vụ không được phê duyệt." => "task not approved.",
    "nhiệm vụ tìm thấy" => "tasks found",
    
    // Staff and employees
    "Nhân sự" => "Staff",
    "Nhân sự tham gia" => "Participating staff",
    "Nhân sự thực hiện" => "Executing staff",
    "Nhân viên" => "Employee",
    "Nhãn hiệu" => "Trademark",
    "Nhóm" => "Group",
    "Nhóm công việc" => "Job group",
    "Nhóm: " => "Group: ",
    "Nhúng link từ one drive" => "Embed link from one drive",
    "Như vậy, bạn còn" => "So, you have",
    
    // Input and forms
    "Nhập deadline cho công việc này" => "Enter deadline for this job",
    "Nhập lại mật khẩu mới" => "Re-enter new password",
    "Nhập thông tin công việc" => "Enter job information",
    "Nhập thông tin khách hàng mới" => "Enter new customer information",
    "Nhập thông tin đối tác" => "Enter partner information",
    "Nhập thông tin đối tác mới" => "Enter new partner information",
    "Notification" => "Notification",
    "Notifications" => "Notifications",
    
    // Time units
    "năm" => "years",
    "Năm gia hạn" => "Renewal year",
    "Nội dung" => "Content",
    "Nội dung chi tiết" => "Detailed content",
    "Nội dung công việc" => "Job content", 
    "Nội dung khác" => "Other content",
    "Nội dung nhiệm vụ" => "Task content",
    "Nội dung:" => "Content:",
    
    // Categories and classification
    "Phân loại" => "Category",
    "Phân loại VIP" => "VIP category",
    "Phân loại:" => "Category:",
    "Phê duyệt nhiệm vụ (task)" => "Approve task (task)",
    "phút" => "minutes",
    "POST renewal to renewal system by API" => "POST renewal to renewal system by API",
    "POST user to renewal system by API" => "POST user to renewal system by API",
    "Quay lại" => "Go back",
    "Quản lý công việc" => "Job Management",
    
    // Location and country
    "Quốc gia" => "Country",
    "Quốc gia nộp" => "Filing country",
    "sang" => "to",
    "sang hệ thống gia hạn" => "to renewal system",
    "Search nâng cao" => "Advanced Search",
    "STT" => "No.",
    "Sáng chế" => "Patent",
    
    // Numbers and quantities
    "Số lượng nhóm" => "Number of groups",
    "Số lượng nhóm: " => "Number of groups: ",
    "Số lượng phương án" => "Number of options",
    "Số lượng phương án: " => "Number of options: ",
    "Số lượng yêu cầu bảo hộ" => "Number of protection claims",
    "Số lượng yêu cầu bảo hộ độc lập" => "Number of independent protection claims",
    "Số lượng yêu cầu bảo hộ độc lập: " => "Number of independent protection claims: ",
    "Số lượng yêu cầu bảo hộ: " => "Number of protection claims: ",
    "Số REF của mình: " => "Our REF: ",
    "Số REF của đối tác: " => "Partner REF: ",
    "Số REF:" => "REF No.:",
    "Số REF: " => "REF No.: ",
    "Số tiền" => "Amount",
    "Số tiền còn lại" => "Remaining amount",
    "Số điện thoại" => "Phone number",
    "Số đơn: " => "Application No.: ",
    "Số đầu việc" => "Number of jobs",
    "Sổ cái tài chính" => "Financial ledger",
    
    // Edit and modify
    "Sửa" => "Edit",
    "Sửa công việc" => "Edit job",
    "Sửa deadline" => "Edit deadline",
    "Sửa deadline và người xử lý" => "Edit deadline and handler", 
    "Sửa nội dung nhiệm vụ" => "Edit task content",
    "Sửa thông tin khách hàng" => "Edit customer information",
    "Sửa thông tin nhân viên" => "Edit employee information",
    "Sửa thông tin đối tác" => "Edit partner information",
    "Task" => "Task",
    "Tasks" => "Tasks",
    
    // Finance
    "Thu" => "Income",
    "Thu chi USD" => "Income/Expense USD",
    "Thu chi VND" => "Income/Expense VND",
    "thành công" => "successful",
    "tháng" => "months",
    
    // Add new entries
    "Thêm mới khách hàng" => "Add new customer",
    "Thêm mới nhân viên" => "Add new employee",
    "Thêm mới partner (đối tác)" => "Add new partner",
    "Thêm mới thu chi" => "Add new income/expense",
    "Thêm nhiệm vụ" => "Add task",
    "Thêm nhiệm vụ mới" => "Add new task",
    
    // Notifications
    "Thông báo cho khách hàng" => "Notify customer", 
    "Thông báo mới" => "New notification",
    "Thông báo về việc thay đổi nhân sự" => "Notification about staff changes",
    "Thông tin" => "Information",
    "Thông tin bổ sung" => "Additional information",
    "Thông tin cá nhân" => "Personal information",
    "Thông tin công việc" => "Job information",
    "Thông tin kiểu dáng" => "Design information",
    "Thông tin nhãn hiệu" => "Trademark information",
    "Thông tin đối tác" => "Partner information",
    "Thông tin đối tác/khách hàng" => "Partner/Customer information",
    
    // Statistics
    "Thống kê" => "Statistics",
    "Thống kê giá trị công việc theo partner" => "Job value statistics by partner",
    "Thống kê số lượng công việc theo user" => "Job quantity statistics by user",
    "Thời gian tuỳ chỉnh" => "Custom time",
    "Thời gian:" => "Time:",
    "Thời hạn để xử lý công việc này là" => "The deadline to handle this job is",
    "Tiêu đề:" => "Title:",
    "Tiềm năng" => "Potential",
    "Tiền còn nợ" => "Remaining debt",
    "Toàn thời gian" => "Full time",
    
    // Pages and templates
    "Trang job dành cho đối tác" => "Job page for partners",
    "Trang thành viên" => "Member page",
    "Trân trọng, " => "Best regards, ",
    "trước" => "ago",
    "Trường Mã đối tác và email là bắt buộc phải có, các trường khác có thể để trống" => "Partner code and email fields are required, other fields can be left blank",
    "Trạng thái" => "Status",
    "Trạng thái nhiệm vụ" => "Task status",
    "tuần" => "weeks",
    
    // Finance and money
    "Tài chính" => "Finance",
    "Tài chính công việc" => "Job finance",
    "Tài liệu đi kèm" => "Accompanying documents",
    
    // Names and titles
    "Tên" => "Name",
    "Tên công ty" => "Company name",
    "Tên công ty " => "Company name ",
    "Tên công ty/Tên khách" => "Company name/Customer name",
    "Tên công ty/tổ chức" => "Company/Organization name",
    "Tên công việc" => "Job name",
    "Tên nhiệm vụ" => "Task name",
    "tên nhiệm vụ" => "task name",
    "Tên nhân sự" => "Staff name",
    "Tên nhãn hiệu" => "Trademark name",
    "Tên nhãn hiệu: " => "Trademark name: ",
    "Tên đăng nhập" => "Username",
    "Tên đối tác" => "Partner name", 
    "Tên đối tác / cty" => "Partner name / company",
    
    // Search and find
    "Tìm kiếm" => "Search",
    "tìm thấy" => "found",
    "Tình trạng" => "Condition",
    "Tùy biến chung" => "General customization",
    
    // Create new
    "Tạo công việc mới" => "Create new job",
    "Tạo file excel đúng theo mẫu trên" => "Create excel file according to above template",
    "Tạo job mới" => "Create new job",
    "Tạo khách hàng mới" => "Create new customer",
    "Tạo mới" => "Create new",
    "Tạo mới và sửa mẫu mail" => "Create and edit email template",
    "Tạo nhân sự mới" => "Create new staff",
    "Tạo phiếu thu chi" => "Create income/expense receipt",
    "Tạo phiếu thu chi mới" => "Create new income/expense receipt",
    "Tạo phiếu thu/chi" => "Create income/expense receipt",
    "Tạo đầu công việc mới" => "Create new main job",
    "Tạo đối tác mới" => "Create new partner",
    "Tạo đối tác nước ngoài mới" => "Create new foreign partner",
    
    // All and totals
    "Tất cả" => "All",
    "Tất cả các loại" => "All types",
    "Tất cả danh mục" => "All categories",
    "Tổng" => "Total",
    "Tổng chi" => "Total expenses",
    "Tổng giá trị" => "Total value",
    "Tổng số công việc" => "Total jobs",
    "Tổng số khách" => "Total customers",
    "Tổng số nhiệm vụ" => "Total tasks",
    "Tổng số tiền" => "Total amount",
    "Tổng số đối tác" => "Total partners",
    "Tổng thu" => "Total income",
    "Tổng tiền cần thanh toán" => "Total amount to pay",
    
    // Reject and decline
    "Từ chối phê duyệt nhiệm vụ" => "Reject task approval",
    "Từ chối phê duyệt đầu việc: " => "Reject job approval: ",
    "Từ khóa:" => "Keywords:",
    
    // Ratios and percentages
    "Tỷ lệ các công việc đã chốt" => "Ratio of completed jobs",
    "Tỷ lệ công việc tiềm năng" => "Potential job ratio",
    "Tỷ lệ đối tác" => "Partner ratio",
    "Tỷ lệ đối tác tiềm năng" => "Potential partner ratio",
    "Tỷ lệ đối tác đã chốt" => "Completed partner ratio",
    
    // Roles and examples
    "Vai trò" => "Role",
    "VD: Nhãn hiệu 9OUTFIT" => "Example: 9OUTFIT Trademark",
    "Việc khác" => "Other work",
    "vừa xong" => "just finished",
    
    // View and check
    "Xem chi tiết việc" => "View job details",
    "Xem tất cả" => "View all",
    "Xoá bài viết theo thời gian" => "Delete posts by time",
    "Xác nhận trạng thái hoàn thành nhiệm vụ" => "Confirm task completion status",
    "Xảy ra lỗi, không thể cập nhật." => "Error occurred, cannot update.",
    
    // Completed and done states
    "Đã chi" => "Spent",
    "Đã chốt" => "Completed",
    "Đã cập nhật" => "Updated",
    "Đã sửa thông tin thành công" => "Successfully updated information",
    "Đã thanh toán" => "Paid",
    "Đã thu" => "Collected",
    "Đã tạm ứng cho đối tác nước ngoài" => "Advanced payment to foreign partner",
    "Đã tạo công việc mới thành công" => "Successfully created new job",
    "Đã tạo khách hàng thành công" => "Successfully created customer",
    "Đã tạo tài khoản thành công" => "Successfully created account",
    "Đã tạo đối tác mới" => "Created new partner",
    "Đã đổi mật khẩu thành công" => "Successfully changed password",
    
    // Login and authentication
    "Đăng nhập" => "Login",
    "Đăng xuất" => "Logout",
    "Để trống sẽ tự sinh số REF" => "Leave blank to auto-generate REF number",
    "Địa chỉ" => "Address",
    
    // Partners and customers
    "Đối tác" => "Partner",
    "Đối tác gửi việc" => "Job sender partner",
    "Đối tác nhận việc" => "Job receiver partner",
    "Đối tác nước ngoài" => "Foreign partner",
    "Đối tác trên mỗi quốc gia" => "Partners per country",
    "Đối tác, khách hàng" => "Partners, customers",
    "Đổi hình" => "Change photo",
    "Đổi mật khẩu" => "Change password",
    
    // Update and change states
    "đã cập nhật" => "updated",
    "đã cập nhật thông tin nhiệm vụ." => "updated task information.",
    "đã thay đổi trạng thái thành" => "changed status to",
    "đã được giao là người quản lý việc" => "assigned as job manager",
    "đã được giao là người xử lý việc" => "assigned as job handler",
    "đến hạn trả lời hôm nay và" => "due to respond today and",
    
    // Additional missing translations from PO file
    "Nguồn đầu việc" => "Job source",
    "Số bằng" => "Certificate number",
    "Số bằng: " => "Certificate number: ",
    "Ngày cấp bằng" => "Certificate date",
    "Ngày cấp bằng: " => "Certificate date: ",
    "Ngày nộp đơn" => "Application date", 
    "Ngày nộp đơn: " => "Application date: ",
    "Số đơn" => "Application number",
    "Số đơn: " => "Application number: ",
    "Số REF của mình" => "Our REF number",
    "Số REF của đối tác" => "Partner REF number",
    "Mã code để truy cập đầu việc" => "Access code for task",
    "Email đối tác" => "Partner email",
    "Sử dụng mã code dưới đây để soạn nội dung email" => "Use the codes below to compose email content",
    
    // Advanced search and filtering
    "Search nâng cao" => "Advanced search",
    "Lọc theo ngày:" => "Filter by date:",
    "Lọc theo trạng thái:" => "Filter by status:",
    "Lọc theo người thực hiện:" => "Filter by assignee:",
    
    // Additional common terms
    "Phí dịch vụ" => "Service fee",
    "Phí chính phủ" => "Government fee",
    "Phí tổng cộng" => "Total fee",
    "Tỷ giá" => "Exchange rate",
    "Đơn vị tiền tệ" => "Currency unit",
    "Số lượng" => "Quantity",
    "Đơn giá" => "Unit price",
    "Thành tiền" => "Total amount",
    
    // Status updates
    "Đã gửi" => "Sent",
    "Đã nhận" => "Received", 
    "Đang xử lý" => "Processing",
    "Chờ xử lý" => "Pending",
    "Đã xử lý" => "Processed",
    "Đã hủy" => "Cancelled",
    "Tạm dừng" => "Paused",
    "Đang chờ" => "Waiting",
    
    // User interface elements
    "Chọn tất cả" => "Select all",
    "Bỏ chọn tất cả" => "Deselect all",
    "Xuất dữ liệu" => "Export data",
    "Nhập dữ liệu" => "Import data",
    "Sao lưu" => "Backup",
    "Khôi phục" => "Restore",
    "Làm mới" => "Refresh",
    "Tải lại" => "Reload",
    
    // Validation messages
    "Trường này là bắt buộc" => "This field is required",
    "Định dạng email không đúng" => "Invalid email format",
    "Số điện thoại không đúng định dạng" => "Invalid phone number format",
    "Mật khẩu phải có ít nhất 6 ký tự" => "Password must be at least 6 characters",
    "Hai mật khẩu không khớp" => "Passwords do not match",
    
    // Time and date formats
    "Hôm nay" => "Today",
    "Hôm qua" => "Yesterday", 
    "Ngày mai" => "Tomorrow",
    "Tuần này" => "This week",
    "Tuần trước" => "Last week",
    "Tuần sau" => "Next week",
    "Tháng này" => "This month",
    "Tháng trước" => "Last month",
    "Tháng sau" => "Next month",
    "Năm này" => "This year",
    "Năm trước" => "Last year",
    "Năm sau" => "Next year",
    
    // Additional work-related terms
    "Công việc ưu tiên" => "Priority task",
    "Công việc khẩn cấp" => "Urgent task",
    "Công việc thường" => "Regular task",
    "Độ ưu tiên cao" => "High priority",
    "Độ ưu tiên trung bình" => "Medium priority", 
    "Độ ưu tiên thấp" => "Low priority",
    "Không ưu tiên" => "No priority",
    
    // File operations
    "Tải lên thành công" => "Upload successful",
    "Tải lên thất bại" => "Upload failed",
    "File không đúng định dạng" => "Invalid file format",
    "File quá lớn" => "File too large",
    "Không thể đọc file" => "Cannot read file",
    "File đã tồn tại" => "File already exists",
    
    // Department and organization
    "Bộ phận" => "Department",
    "Phòng ban" => "Department",
    "Chi nhánh" => "Branch",
    "Trụ sở chính" => "Headquarters",
    "Văn phòng đại diện" => "Representative office",
    "Khu vực" => "Region",
    "Miền Bắc" => "Northern region",
    "Miền Nam" => "Southern region",
    "Miền Trung" => "Central region",
    
    // Communication
    "Gửi thông báo" => "Send notification",
    "Gửi email" => "Send email",
    "Gửi tin nhắn" => "Send message",
    "Nhận thông báo" => "Receive notification",
    "Đọc thông báo" => "Read notification",
    "Đánh dấu đã đọc" => "Mark as read",
    "Đánh dấu chưa đọc" => "Mark as unread",
    
    // Specific missing translations from PO analysis
    "Lưu ý: nếu là đầu việc lớn có nhiều nhiệm vụ con thì bỏ qua trường thông tin này." => "Note: if it's a big job with many sub-tasks, skip this information field.",
    "Lần nhắc thứ " => "Reminder ",
    "Lần nhắc thứ 4 đối với đầu việc:" => "4th reminder for job:",
    "Như vậy, bạn còn" => "So, you have",
    "ngày để trả lời." => "days to respond.",
    "cần gửi báo cáo cho người quản lý về lý do chưa trả lời này." => "need to send report to manager about the reason for not responding.",
    "cần trả lời ngay." => "need to respond immediately.",
    "chưa trả lời." => "not responded yet.",
    "sang hệ thống gia hạn" => "to renewal system",
    "thành công" => "successful",
    "vừa xong" => "just finished",
    "trước" => "ago",
    "đã cập nhật" => "updated", 
    "đã cập nhật thông tin nhiệm vụ." => "updated task information.",
    "đã thay đổi trạng thái thành" => "changed status to",
    "đã được giao là người quản lý việc" => "assigned as job manager",
    "đã được giao là người xử lý việc" => "assigned as job handler"
    ];

    // Perform PO file translation
    $translated_text = translatePOContent($input_text, $translations);
    
    $thongbao = '<div class="alert alert-success" role="alert">
                        <i class="fa fa-check"></i> ' . __('Đã dịch thành công', 'qlcv') . '
                    </div>';
}

// Function to translate PO file content
function translatePOContent($content, $translations) {
    // Remove slashes that might be added by PHP
    $content = stripslashes($content);
    
    // Split content into lines for processing
    $lines = explode("\n", $content);
    $result = [];
    $i = 0;
    
    while ($i < count($lines)) {
        $line = trim($lines[$i]);
        
        // Check if this line contains msgid with more flexible pattern
        if (preg_match('/^msgid\s+"(.*)"\s*$/', $line, $matches)) {
            $msgid_text = $matches[1];
            $result[] = $lines[$i]; // Keep the original msgid line as is
            $i++;
            
            // Look for the corresponding msgstr line
            if ($i < count($lines)) {
                $next_line = trim($lines[$i]);
                if (preg_match('/^msgstr\s+""\s*$/', $next_line)) {
                    // This is an empty msgstr that needs translation
                    if (isset($translations[$msgid_text])) {
                        $translation = addslashes($translations[$msgid_text]);
                        $result[] = 'msgstr "' . $translation . '"';
                    } else {
                        // Keep empty if no translation found
                        $result[] = $lines[$i];
                    }
                } else {
                    // Not an empty msgstr, keep as is
                    $result[] = $lines[$i];
                }
            }
        } else {
            // Regular line (comments, headers, etc.), keep as is
            $result[] = $lines[$i];
        }
        $i++;
    }
    
    return implode("\n", $result);
}

get_header();
get_sidebar();
?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <!-- Page Heading Start -->
        <div class="col-12 col-lg-12 mb-20">
            <div class="page-heading">
                <h3 class="title"><?php _e('Công cụ dịch file PO tiếng Việt sang tiếng Anh', 'qlcv'); ?></h3>
            </div>
        </div><!-- Page Heading End -->

        <div class="col-12 mb-30">
            <div class="box">
                <div class="box-body">
                    <?php
                    if ($thongbao) {
                        echo $thongbao;
                    }
                    ?>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fa fa-info-circle"></i> 
                        <strong><?php _e('Hướng dẫn sử dụng:', 'qlcv'); ?></strong><br>
                        <?php _e('Nhập nội dung file PO (msgid/msgstr) vào ô bên dưới và nhấn "Dịch" để tự động dịch các dòng msgstr trống.', 'qlcv'); ?><br>
                        <strong><?php _e('Ví dụ đầu vào:', 'qlcv'); ?></strong><br>
                        <code>msgid "Danh sách nhiệm vụ chờ phê duyệt (task)"<br>msgstr ""</code><br>
                        <strong><?php _e('Ví dụ đầu ra:', 'qlcv'); ?></strong><br>
                        <code>msgid "Danh sách nhiệm vụ chờ phê duyệt (task)"<br>msgstr "Pending task list (task)"</code>
                    </div>

                    <div>
                        <form action="#" method="POST" class="row">
                            <div class="col-lg-3 form_title lh45"><?php _e('Nội dung file PO:', 'qlcv'); ?></div>
                            <div class="col-lg-9 col-12 mb-20">
                                <textarea 
                                    class="form-control" 
                                    name="text_to_translate" 
                                    rows="12"
                                    placeholder="<?php _e('Dán nội dung file PO cần dịch vào đây...', 'qlcv'); ?>&#10;<?php _e('Ví dụ:', 'qlcv'); ?>&#10;#. Name of the template&#10;msgid &quot;Danh sách nhiệm vụ chờ phê duyệt (task)&quot;&#10;msgstr &quot;&quot;&#10;&#10;msgid &quot;Tên công ty&quot;&#10;msgstr &quot;&quot;"
                                    style="font-family: 'Courier New', monospace; font-size: 13px;"><?php echo $input_text; ?></textarea>
                            </div>

                            <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>

                            <div class="col-lg-3"></div>
                            <div class="col-lg-9 col-12 mb-20">
                                <input type="submit" class="button button-primary" value="<?php _e('🚀 Dịch file PO', 'qlcv'); ?>">
                                <?php if (!empty($translated_text)): ?>
                                    <button type="button" class="button button-secondary ml-10" onclick="copyToClipboard()"><?php _e('📋 Sao chép kết quả', 'qlcv'); ?></button>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($translated_text)): ?>
                            <div class="col-12 mb-20">
                                <hr style="margin: 20px 0;">
                                <div class="alert alert-success" role="alert">
                                    <i class="fa fa-check-circle"></i> 
                                    <strong><?php _e('Kết quả dịch:', 'qlcv'); ?></strong>
                                    <div style="margin-top: 10px; font-size: 14px; color: #666;">
                                        <?php _e('Tổng từ điển:', 'qlcv'); ?> <?php echo count($translations); ?> <?php _e('cặp từ', 'qlcv'); ?> | 
                                        <?php _e('Độ dài đầu vào:', 'qlcv'); ?> <?php echo strlen($input_text); ?> <?php _e('ký tự', 'qlcv'); ?> | 
                                        <?php _e('Độ dài đầu ra:', 'qlcv'); ?> <?php echo strlen($translated_text); ?> <?php _e('ký tự', 'qlcv'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 form_title lh45"><?php _e('File PO đã dịch:', 'qlcv'); ?></div>
                            <div class="col-lg-9 col-12 mb-20">
                                <textarea 
                                    id="translated_result"
                                    class="form-control" 
                                    rows="12"
                                    readonly
                                    onclick="this.select()"
                                    style="font-family: 'Courier New', monospace; font-size: 13px; background-color: #f8f9fa; border-color: #28a745;"><?php echo stripslashes($translated_text); ?></textarea>
                            </div>
                            <?php endif; ?>

                        </form>
                    </div>
                    
                    <?php if (!empty($translated_text)): ?>
                    <div class="alert alert-light" role="alert">
                        <i class="fa fa-lightbulb-o"></i> 
                        <strong><?php _e('Lưu ý:', 'qlcv'); ?></strong> 
                        <?php _e('Công cụ này sử dụng từ điển đã định sẵn với', 'qlcv'); ?> <?php echo count($translations); ?> <?php _e('cặp từ tiếng Việt-Anh để dịch tự động các dòng msgstr trống trong file PO. Phù hợp cho việc dịch theme WordPress.', 'qlcv'); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->

<script>
function copyToClipboard() {
    const textarea = document.getElementById('translated_result');
    textarea.select();
    textarea.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        alert('<?php _e('✅ Đã sao chép văn bản đã dịch vào clipboard!', 'qlcv'); ?>');
    } catch (err) {
        alert('<?php _e('❌ Không thể sao chép. Vui lòng chọn và sao chép thủ công.', 'qlcv'); ?>');
    }
}

// Auto-focus on the input textarea when page loads
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('textarea[name="text_to_translate"]');
    if (textarea && !textarea.value) {
        textarea.focus();
    }
});

// Add keyboard shortcut (Ctrl+Enter) to submit form
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        document.querySelector('form').submit();
    }
});
</script>

<?php
get_footer();
?>
