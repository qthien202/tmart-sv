<?php

$table_validate = [
    # Users
    "users.fk"                         => [
        "EN" => "Can not delete [{0}] because using in plants",
        "VI" => "[{0}] không thể xóa do đang sử dụng trong danh sách cây trồng",
    ],
    "users.login-invalid"              => [
        "EN" => "Phone or password is invalid!",
        "VI" => "Số điện thoại hoặc mật khẩu không đúng!",
    ],
    "users.admin-login-invalid"        => [
        "EN" => "User or password is invalid!",
        "VI" => "Tên đăng nhập hoặc mật khẩu không đúng!",
    ],
    "users.admin-login-invalid-device" => [
        "EN" => "User deny login on APP!",
        "VI" => "Tài khoản này không được login trên APP!",
    ],
    "users.user-inactive"              => [
        "EN" => "The user is inactive",
        "VI" => "Tài khoản chưa được kích hoạt",
    ],
    "users.login-not-exist"            => [
        "EN" => "[{0}] is not exist or not activate.",
        "VI" => "[{0}] chưa được đăng ký hoặc chưa kích hoạt.",
    ],
    "users.login-not-allow"            => [
        "EN" => "[{0}] is not allow to access.",
        "VI" => "[{0}] không được phép.",
    ],
    "users.not-exist"                  => [
        "EN" => "[{0}] not exist",
        "VI" => "[{0}] không tồn tại",
    ],
    "users.existed"                    => [
        "EN" => "Culture of [{0}] not exist",
        "VI" => "Thông tin canh tác của [{0}] không tồn tại",
    ],
    "users.create-success"             => [
        "EN" => "[{0}] has created successful",
        "VI" => "[{0}] vừa được tạo thành công",
    ],
    "users.update-success"             => [
        "EN" => "[{0}] has updated successful",
        "VI" => "[{0}] vừa được chỉnh sửa thành công",
    ],
    "users.delete-success"             => [
        "EN" => "[{0}] has deleted",
        "VI" => "Bạn vừa xóa [{0}] thành công",
    ],
    "users.register-success"           => [
        "EN" => "[{0}] has register successful",
        "VI" => "[{0}] đã được đăng ký thành công",
    ],
    "users.active-success"             => [
        "EN" => "Account [{0}] has activated successful",
        "VI" => "Tài khoản [{0}] vừa được kích hoạt thành công",
    ],
    "users.inactive-success"           => [
        "EN" => "Account [{0}] has inactivated successful",
        "VI" => "Tài khoản [{0}] vừa được vô hiệu hóa thành công",
    ],
    "users.change-password"            => [
        "EN" => "Change password successful",
        "VI" => "Thay đổi mật khẩu thành công",
    ],
    "users.reset-password-success"     => [
        "EN" => "Reset password successful",
        "VI" => "Khôi phục mật khẩu thành công",
    ],

    "users.check-fail"          => [
        "EN" => "Account is not exist or not activate.",
        "VI" => "Tài khoản không tồn tại hoặc chưa được kích hoạt.",
    ],
    # Roles
    "roles.fk"                  => [
        "EN" => "Can not delete [{0}] because using in roles",
        "VI" => "[{0}] không thể xóa do đang sử dụng trong danh sách các vai trò của ứng dụng",
    ],
    "roles.create-success"      => [
        "EN" => "[{0}] has created successful",
        "VI" => "[{0}] vừa được tạo thành công",
    ],
    "roles.update-success"      => [
        "EN" => "[{0}] has updated successful",
        "VI" => "[{0}] vừa được chỉnh sửa thành công",
    ],
    "roles.delete-success"      => [
        "EN" => "[{0}] has deleted",
        "VI" => "Bạn vừa xóa [{0}] thành công",
    ],
    # Permission
    "permission.fk"             => [
        "EN" => "Can not delete [{0}] because using in master_data",
        "VI" => "[{0}] không thể xóa do đang sử dụng",
    ],
    "permission.create-success" => [
        "EN" => "[{0}] has created successful",
        "VI" => "[{0}] vừa được tạo thành công",
    ],
    "permission.update-success" => [
        "EN" => "[{0}] has updated successful",
        "VI" => "[{0}] vừa được chỉnh sửa thành công",
    ],
    "permission.delete-success" => [
        "EN" => "[{0}] has deleted",
        "VI" => "Bạn vừa xóa [{0}] thành công",
    ]
];
