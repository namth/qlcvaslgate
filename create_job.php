<?php
/*
    Template Name: Tạo job mới
*/
get_header();

get_sidebar();

if (isset($_GET['type'])) {
    $type = $_GET['type'];
}
?>

<!-- Content Body Start -->
<div class="content-body">

    <!-- Page Headings Start -->
    <div class="row justify-content-between align-items-center mb-10">

        <div class="col-12 mb-30">
            <div class="box">
                <div class="box-head">
                    <h3 class="title"><?php the_title(); ?></h3>
                </div>
                <div class="box-body">

                    <div class="smart-wizard" id="create_new_job">
                        <ul>
                            <li><a href="#step-0">1. <?php _e('Thông tin công việc', 'qlcv'); ?></a></li>
                            <li><a href="#step-1">2. <?php _e('Đối tác, khách hàng', 'qlcv'); ?></a></li>
                            <li><a href="#step-2">3. <?php _e('Tài chính', 'qlcv'); ?></a></li>
                            <li><a href="#step-3">4. <?php _e('Nhân sự thực hiện', 'qlcv'); ?></a></li>
                        </ul>

                        <div>
                            <div id="step-0">
                                <div class="row mbn-20">
                                    <div class="col-12 mb-20">
                                        <h4><?php _e('Nhập thông tin công việc', 'qlcv'); ?></h4>
                                    </div>
                                    <div class="col-12">
                                        <form action="" method="POST" id="new_job" class="row">
                                            <div class="col-lg-3 form_title text-left text-lg-right"><?php _e('Nguồn đầu việc', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                            <div class="col-lg-6 col-12 mb-20">
                                                <div class="form-group">
                                                    <?php
                                                    $terms = get_terms(array(
                                                        'taxonomy' => 'post_tag',
                                                        'hide_empty' => false,
                                                    ));
                                                    foreach ($terms as $value) {
                                                        echo '<label class="inline"><input type="radio" name="nguon_dau_viec" value="' . $value->name . '">' . $value->name . '</label>';
                                                    }
                                                    ?>
                                                    <div id="select_partner_1" style="display: none;">
                                                        <select class="form-control select2-tags mb-20" name="partner_1">
                                                            <option value="">-- <?php _e('Chọn đối tác giới thiệu', 'qlcv'); ?> --</option>
                                                            <?php
                                                            $args   = array(
                                                                'role'      => 'partner', /*subscriber, contributor, author*/
                                                            );
                                                            $query = get_users($args);

                                                            if ($query) {
                                                                foreach ($query as $user) {
                                                                    $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
                                                                    echo "<option value='" . $user->ID . "'>" . $ten_cong_ty . " (" . $user->user_email . ")</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3"></div>

                                            <div class="col-lg-3 form_title text-left text-lg-right"><?php _e('Phân loại', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                            <div class="col-lg-6 col-12 mb-20">
                                                <div class="form-group">
                                                    <label class="inline"><input type="radio" name="tiem_nang" value="0" checked=""><?php _e('Đã chốt', 'qlcv'); ?></label>
                                                    <label class="inline"><input type="radio" name="tiem_nang" value="1"><?php _e('Tiềm năng', 'qlcv'); ?></label>
                                                </div>
                                            </div>
                                            <div class="col-lg-3"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Tên công việc', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                            <div class="col-lg-6 col-12 mb-20"><input type="text" placeholder="<?php _e('VD: Nhãn hiệu 9OUTFIT', 'qlcv'); ?>" class="form-control" name="job_name"></div>
                                            <div class="col-lg-3"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Số REF của đối tác', 'qlcv'); ?></div>
                                            <div class="col-lg-6 col-12 mb-20"><input type="text" placeholder="<?php _e('Số REF của đối tác', 'qlcv'); ?>" class="form-control" name="partner_ref"></div>
                                            <div class="col-lg-3"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Số REF của mình', 'qlcv'); ?></div>
                                            <div class="col-lg-6 col-12 mb-20"><input type="text" placeholder="<?php _e('Để trống sẽ tự sinh số REF', 'qlcv'); ?>" class="form-control" name="our_ref"></div>
                                            <div class="col-lg-3"></div>

                                            <div class="col-lg-3 form_title text-left text-lg-right"><?php _e('Thông tin', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                            <div class="col-lg-8 col-12 mb-20">
                                                <div class="form-group">
                                                    <?php
                                                    if (!$type) {
                                                        $type = "Nhãn hiệu";
                                                    ?>
                                                        <ul class="nav nav-pills mb-15" id="choose_group">
                                                            <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#nhanhieu" data-group="Nhãn hiệu"><?php _e('Nhãn hiệu', 'qlcv'); ?></a></li>
                                                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#kieudang" data-group="Kiểu dáng"><?php _e('Kiểu dáng', 'qlcv'); ?></a></li>
                                                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#sangche" data-group="Sáng chế"><?php _e('Sáng chế', 'qlcv'); ?></a></li>
                                                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#vieckhac" data-group="Bản quyền"><?php _e('Bản quyền', 'qlcv'); ?></a></li>
                                                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#vieckhac" data-group="Franchise">Franchise</a></li>
                                                            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#vieckhac" data-group="Việc khác"><?php _e('Việc khác', 'qlcv'); ?></a></li>
                                                        </ul>
                                                    <?php
                                                    }

                                                    switch ($type) {
                                                        case 'Nhãn hiệu':
                                                            $class_nhan_hieu    = "active show";
                                                            $class_kieu_dang    = "";
                                                            $class_sang_che     = "";
                                                            break;

                                                        case 'Kiểu dáng':
                                                            $class_nhan_hieu    = "";
                                                            $class_kieu_dang    = "active show";
                                                            $class_sang_che     = "";
                                                            break;

                                                        case 'Sáng chế':
                                                            $class_nhan_hieu    = "";
                                                            $class_kieu_dang    = "";
                                                            $class_sang_che     = "active show";
                                                            break;

                                                        default:
                                                            $class_nhan_hieu    = "";
                                                            $class_kieu_dang    = "";
                                                            $class_sang_che     = "";
                                                            $class_viec_khac    = "active show";
                                                            break;
                                                    }
                                                    ?>
                                                    <input type="hidden" name="danh_muc" value="<?php echo $type; ?>">
                                                    <div class="tab-content">
                                                        <div class="tab-pane fade <?php echo $class_nhan_hieu; ?>" id="nhanhieu">
                                                            <input type="text" placeholder="<?php _e('Tên nhãn hiệu', 'qlcv'); ?>" class="form-control mb-10" name="brand_name">
                                                            <input type="text" placeholder="<?php _e('Nhóm', 'qlcv'); ?>" class="form-control mb-10" name="brand_group">
                                                            <input type="text" placeholder="<?php _e('Số lượng nhóm', 'qlcv'); ?>" class="form-control mb-10" name="brand_number_group">
                                                            <input class="dropify" type="file" name="file_upload">
                                                        </div>
                                                        <div class="tab-pane fade <?php echo $class_kieu_dang; ?>" id="kieudang">
                                                            <input type="text" placeholder="<?php _e('Link tới bộ ảnh', 'qlcv'); ?>" class="form-control mb-10" name="kdang_pic">
                                                            <input type="text" placeholder="<?php _e('Link tới bản mô tả của bộ ảnh', 'qlcv'); ?>" class="form-control mb-10" name="kdang_info">
                                                            <input type="text" placeholder="<?php _e('Số lượng phương án', 'qlcv'); ?>" class="form-control mb-10" name="kdang_phuongan">
                                                        </div>
                                                        <div class="tab-pane fade <?php echo $class_sang_che; ?>" id="sangche">
                                                            <input type="text" placeholder="<?php _e('Link tới bản mô tả sáng chế', 'qlcv'); ?>" class="form-control mb-10" name="sche_info">
                                                            <input type="text" placeholder="<?php _e('Số lượng yêu cầu bảo hộ', 'qlcv'); ?>" class="form-control mb-10" name="sche_request_1">
                                                            <input type="text" placeholder="<?php _e('Số lượng yêu cầu bảo hộ độc lập', 'qlcv'); ?>" class="form-control mb-10" name="sche_request_2">
                                                        </div>
                                                        <div class="tab-pane fade <?php echo $class_viec_khac; ?>" id="vieckhac">
                                                            <span class="form-help-text"><?php _e('Nhập deadline cho công việc này', 'qlcv'); ?></span>
                                                            <input type="text" class="form-control" value="" name="deadline" placeholder="Deadline: dd/mm/yyyy" data-mask="99/99/9999">
                                                            <span class="form-help-text text-danger"><?php _e('Lưu ý: nếu là đầu việc lớn có nhiều nhiệm vụ con thì bỏ qua trường thông tin này.', 'qlcv'); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-1"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Nội dung chi tiết', 'qlcv'); ?></div>
                                            <div class="col-lg-8 col-12 mb-20"><textarea class="form-control summernote" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"></textarea></div>
                                            <div class="col-lg-1"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Lưu ý công việc', 'qlcv'); ?></div>
                                            <div class="col-lg-8 col-12 mb-20"><textarea class="form-control summernote" placeholder="Lưu ý" name="mindful"></textarea></div>
                                            <div class="col-lg-1"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Link file hồ sơ', 'qlcv'); ?></div>
                                            <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Nhúng link từ one drive', 'qlcv'); ?>" name="link_onedrive"></textarea></div>
                                            <div class="col-lg-3"></div>

                                            <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Quốc gia nộp', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                            <div class="col-lg-6 col-12 mb-20">
                                                <select class="form-control select2-tags mb-20" multiple="" name="country">
                                                    <option value="Afghanistan">Afghanistan</option>
                                                    <option value="Albania">Albania</option>
                                                    <option value="Algeria">Algeria</option>
                                                    <option value="American Samoa">American Samoa</option>
                                                    <option value="Andorra">Andorra</option>
                                                    <option value="Angola">Angola</option>
                                                    <option value="Anguilla">Anguilla</option>
                                                    <option value="Antartica">Antarctica</option>
                                                    <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                                    <option value="Argentina">Argentina</option>
                                                    <option value="Armenia">Armenia</option>
                                                    <option value="Aruba">Aruba</option>
                                                    <option value="Australia">Australia</option>
                                                    <option value="Austria">Austria</option>
                                                    <option value="Azerbaijan">Azerbaijan</option>
                                                    <option value="Bahamas">Bahamas</option>
                                                    <option value="Bahrain">Bahrain</option>
                                                    <option value="Bangladesh">Bangladesh</option>
                                                    <option value="Barbados">Barbados</option>
                                                    <option value="Belarus">Belarus</option>
                                                    <option value="Belgium">Belgium</option>
                                                    <option value="Belize">Belize</option>
                                                    <option value="Benin">Benin</option>
                                                    <option value="Bermuda">Bermuda</option>
                                                    <option value="Bhutan">Bhutan</option>
                                                    <option value="Bolivia">Bolivia</option>
                                                    <option value="Bosnia and Herzegowina">Bosnia and Herzegowina</option>
                                                    <option value="Botswana">Botswana</option>
                                                    <option value="Bouvet Island">Bouvet Island</option>
                                                    <option value="Brazil">Brazil</option>
                                                    <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                                    <option value="Brunei Darussalam">Brunei Darussalam</option>
                                                    <option value="Bulgaria">Bulgaria</option>
                                                    <option value="Burkina Faso">Burkina Faso</option>
                                                    <option value="Burundi">Burundi</option>
                                                    <option value="Cambodia">Cambodia</option>
                                                    <option value="Cameroon">Cameroon</option>
                                                    <option value="Canada">Canada</option>
                                                    <option value="Cape Verde">Cape Verde</option>
                                                    <option value="Cayman Islands">Cayman Islands</option>
                                                    <option value="Central African Republic">Central African Republic</option>
                                                    <option value="Chad">Chad</option>
                                                    <option value="Chile">Chile</option>
                                                    <option value="China">China</option>
                                                    <option value="Christmas Island">Christmas Island</option>
                                                    <option value="Cocos Islands">Cocos (Keeling) Islands</option>
                                                    <option value="Colombia">Colombia</option>
                                                    <option value="Comoros">Comoros</option>
                                                    <option value="Congo">Congo</option>
                                                    <option value="Congo">Congo, the Democratic Republic of the</option>
                                                    <option value="Cook Islands">Cook Islands</option>
                                                    <option value="Costa Rica">Costa Rica</option>
                                                    <option value="Cota D'Ivoire">Cote d'Ivoire</option>
                                                    <option value="Croatia">Croatia (Hrvatska)</option>
                                                    <option value="Cuba">Cuba</option>
                                                    <option value="Cyprus">Cyprus</option>
                                                    <option value="Czech Republic">Czech Republic</option>
                                                    <option value="Denmark">Denmark</option>
                                                    <option value="Djibouti">Djibouti</option>
                                                    <option value="Dominica">Dominica</option>
                                                    <option value="Dominican Republic">Dominican Republic</option>
                                                    <option value="East Timor">East Timor</option>
                                                    <option value="Ecuador">Ecuador</option>
                                                    <option value="Egypt">Egypt</option>
                                                    <option value="El Salvador">El Salvador</option>
                                                    <option value="Equatorial Guinea">Equatorial Guinea</option>
                                                    <option value="Eritrea">Eritrea</option>
                                                    <option value="Estonia">Estonia</option>
                                                    <option value="Ethiopia">Ethiopia</option>
                                                    <option value="Falkland Islands">Falkland Islands (Malvinas)</option>
                                                    <option value="Faroe Islands">Faroe Islands</option>
                                                    <option value="Fiji">Fiji</option>
                                                    <option value="Finland">Finland</option>
                                                    <option value="France">France</option>
                                                    <option value="France Metropolitan">France, Metropolitan</option>
                                                    <option value="French Guiana">French Guiana</option>
                                                    <option value="French Polynesia">French Polynesia</option>
                                                    <option value="French Southern Territories">French Southern Territories</option>
                                                    <option value="Gabon">Gabon</option>
                                                    <option value="Gambia">Gambia</option>
                                                    <option value="Georgia">Georgia</option>
                                                    <option value="Germany">Germany</option>
                                                    <option value="Ghana">Ghana</option>
                                                    <option value="Gibraltar">Gibraltar</option>
                                                    <option value="Greece">Greece</option>
                                                    <option value="Greenland">Greenland</option>
                                                    <option value="Grenada">Grenada</option>
                                                    <option value="Guadeloupe">Guadeloupe</option>
                                                    <option value="Guam">Guam</option>
                                                    <option value="Guatemala">Guatemala</option>
                                                    <option value="Guinea">Guinea</option>
                                                    <option value="Guinea-Bissau">Guinea-Bissau</option>
                                                    <option value="Guyana">Guyana</option>
                                                    <option value="Haiti">Haiti</option>
                                                    <option value="Heard and McDonald Islands">Heard and Mc Donald Islands</option>
                                                    <option value="Holy See">Holy See (Vatican City State)</option>
                                                    <option value="Honduras">Honduras</option>
                                                    <option value="Hong Kong">Hong Kong</option>
                                                    <option value="Hungary">Hungary</option>
                                                    <option value="Iceland">Iceland</option>
                                                    <option value="India">India</option>
                                                    <option value="Indonesia">Indonesia</option>
                                                    <option value="Iran">Iran (Islamic Republic of)</option>
                                                    <option value="Iraq">Iraq</option>
                                                    <option value="Ireland">Ireland</option>
                                                    <option value="Israel">Israel</option>
                                                    <option value="Italy">Italy</option>
                                                    <option value="Jamaica">Jamaica</option>
                                                    <option value="Japan">Japan</option>
                                                    <option value="Jordan">Jordan</option>
                                                    <option value="Kazakhstan">Kazakhstan</option>
                                                    <option value="Kenya">Kenya</option>
                                                    <option value="Kiribati">Kiribati</option>
                                                    <option value="Democratic People's Republic of Korea">Korea, Democratic People's Republic of</option>
                                                    <option value="Korea">Korea, Republic of</option>
                                                    <option value="Kuwait">Kuwait</option>
                                                    <option value="Kyrgyzstan">Kyrgyzstan</option>
                                                    <option value="Lao">Lao People's Democratic Republic</option>
                                                    <option value="Latvia">Latvia</option>
                                                    <option value="Lebanon">Lebanon</option>
                                                    <option value="Lesotho">Lesotho</option>
                                                    <option value="Liberia">Liberia</option>
                                                    <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                                    <option value="Liechtenstein">Liechtenstein</option>
                                                    <option value="Lithuania">Lithuania</option>
                                                    <option value="Luxembourg">Luxembourg</option>
                                                    <option value="Macau">Macau</option>
                                                    <option value="Macedonia">Macedonia, The Former Yugoslav Republic of</option>
                                                    <option value="Madagascar">Madagascar</option>
                                                    <option value="Malawi">Malawi</option>
                                                    <option value="Malaysia">Malaysia</option>
                                                    <option value="Maldives">Maldives</option>
                                                    <option value="Mali">Mali</option>
                                                    <option value="Malta">Malta</option>
                                                    <option value="Marshall Islands">Marshall Islands</option>
                                                    <option value="Martinique">Martinique</option>
                                                    <option value="Mauritania">Mauritania</option>
                                                    <option value="Mauritius">Mauritius</option>
                                                    <option value="Mayotte">Mayotte</option>
                                                    <option value="Mexico">Mexico</option>
                                                    <option value="Micronesia">Micronesia, Federated States of</option>
                                                    <option value="Moldova">Moldova, Republic of</option>
                                                    <option value="Monaco">Monaco</option>
                                                    <option value="Mongolia">Mongolia</option>
                                                    <option value="Montserrat">Montserrat</option>
                                                    <option value="Morocco">Morocco</option>
                                                    <option value="Mozambique">Mozambique</option>
                                                    <option value="Myanmar">Myanmar</option>
                                                    <option value="Namibia">Namibia</option>
                                                    <option value="Nauru">Nauru</option>
                                                    <option value="Nepal">Nepal</option>
                                                    <option value="Netherlands">Netherlands</option>
                                                    <option value="Netherlands Antilles">Netherlands Antilles</option>
                                                    <option value="New Caledonia">New Caledonia</option>
                                                    <option value="New Zealand">New Zealand</option>
                                                    <option value="Nicaragua">Nicaragua</option>
                                                    <option value="Niger">Niger</option>
                                                    <option value="Nigeria">Nigeria</option>
                                                    <option value="Niue">Niue</option>
                                                    <option value="Norfolk Island">Norfolk Island</option>
                                                    <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                                    <option value="Norway">Norway</option>
                                                    <option value="Oman">Oman</option>
                                                    <option value="Pakistan">Pakistan</option>
                                                    <option value="Palau">Palau</option>
                                                    <option value="Panama">Panama</option>
                                                    <option value="Papua New Guinea">Papua New Guinea</option>
                                                    <option value="Paraguay">Paraguay</option>
                                                    <option value="Peru">Peru</option>
                                                    <option value="Philippines">Philippines</option>
                                                    <option value="Pitcairn">Pitcairn</option>
                                                    <option value="Poland">Poland</option>
                                                    <option value="Portugal">Portugal</option>
                                                    <option value="Puerto Rico">Puerto Rico</option>
                                                    <option value="Qatar">Qatar</option>
                                                    <option value="Reunion">Reunion</option>
                                                    <option value="Romania">Romania</option>
                                                    <option value="Russia">Russian Federation</option>
                                                    <option value="Rwanda">Rwanda</option>
                                                    <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                                    <option value="Saint LUCIA">Saint LUCIA</option>
                                                    <option value="Saint Vincent">Saint Vincent and the Grenadines</option>
                                                    <option value="Samoa">Samoa</option>
                                                    <option value="San Marino">San Marino</option>
                                                    <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                                    <option value="Saudi Arabia">Saudi Arabia</option>
                                                    <option value="Senegal">Senegal</option>
                                                    <option value="Seychelles">Seychelles</option>
                                                    <option value="Sierra">Sierra Leone</option>
                                                    <option value="Singapore">Singapore</option>
                                                    <option value="Slovakia">Slovakia (Slovak Republic)</option>
                                                    <option value="Slovenia">Slovenia</option>
                                                    <option value="Solomon Islands">Solomon Islands</option>
                                                    <option value="Somalia">Somalia</option>
                                                    <option value="South Africa">South Africa</option>
                                                    <option value="South Georgia">South Georgia and the South Sandwich Islands</option>
                                                    <option value="Span">Spain</option>
                                                    <option value="SriLanka">Sri Lanka</option>
                                                    <option value="St. Helena">St. Helena</option>
                                                    <option value="St. Pierre and Miguelon">St. Pierre and Miquelon</option>
                                                    <option value="Sudan">Sudan</option>
                                                    <option value="Suriname">Suriname</option>
                                                    <option value="Svalbard">Svalbard and Jan Mayen Islands</option>
                                                    <option value="Swaziland">Swaziland</option>
                                                    <option value="Sweden">Sweden</option>
                                                    <option value="Switzerland">Switzerland</option>
                                                    <option value="Syria">Syrian Arab Republic</option>
                                                    <option value="Taiwan">Taiwan, Province of China</option>
                                                    <option value="Tajikistan">Tajikistan</option>
                                                    <option value="Tanzania">Tanzania, United Republic of</option>
                                                    <option value="Thailand">Thailand</option>
                                                    <option value="Togo">Togo</option>
                                                    <option value="Tokelau">Tokelau</option>
                                                    <option value="Tonga">Tonga</option>
                                                    <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                                    <option value="Tunisia">Tunisia</option>
                                                    <option value="Turkey">Turkey</option>
                                                    <option value="Turkmenistan">Turkmenistan</option>
                                                    <option value="Turks and Caicos">Turks and Caicos Islands</option>
                                                    <option value="Tuvalu">Tuvalu</option>
                                                    <option value="Uganda">Uganda</option>
                                                    <option value="Ukraine">Ukraine</option>
                                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                                    <option value="United Kingdom">United Kingdom</option>
                                                    <option value="United States">United States</option>
                                                    <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                                                    <option value="Uruguay">Uruguay</option>
                                                    <option value="Uzbekistan">Uzbekistan</option>
                                                    <option value="Vanuatu">Vanuatu</option>
                                                    <option value="Venezuela">Venezuela</option>
                                                    <option value="Vietnam">Vietnam</option>
                                                    <option value="Virgin Islands (British)">Virgin Islands (British)</option>
                                                    <option value="Virgin Islands (U.S)">Virgin Islands (U.S.)</option>
                                                    <option value="Wallis and Futana Islands">Wallis and Futuna Islands</option>
                                                    <option value="Western Sahara">Western Sahara</option>
                                                    <option value="Yemen">Yemen</option>
                                                    <option value="Serbia">Serbia</option>
                                                    <option value="Zambia">Zambia</option>
                                                    <option value="Zimbabwe">Zimbabwe</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3"></div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div id="step-1">
                                <div class="row mbn-20">
                                    <div class="col-12 mb-20">
                                        <h4><?php _e('Chọn đối tác gửi việc từ trong danh sách', 'qlcv'); ?> <span class="text-danger">*</span></h4>
                                        <select class="form-control select2-tags mb-20" name="partner">
                                            <option value="">-- <?php _e('Chọn đối tác gửi việc', 'qlcv'); ?> --</option>
                                            <?php
                                            $args   = array(
                                                'role'      => 'partner', /*subscriber, contributor, author*/
                                            );
                                            $query = get_users($args);

                                            if ($query) {
                                                foreach ($query as $user) {
                                                    $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
                                                    $partner_code   = get_field('partner_code', 'user_' . $user->ID);
                                                    echo "<option value='" . $user->ID . "'>" . $partner_code . " - " . $ten_cong_ty . " (" . $user->user_email . ")</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-20">
                                        <button class="button button-primary create_new_button" data-div="#create_partner"><span><i class="fa fa-user-plus"></i><?php _e('Tạo đối tác mới', 'qlcv'); ?></span></button>
                                        <div id="create_partner" style="display: none;">
                                            <form action="#" method="POST" class="row">
                                                <div class="col-12 mb-20 notification">
                                                    <h4><?php _e('Nhập thông tin đối tác mới', 'qlcv'); ?></h4>
                                                </div>
                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Tên công ty/tổ chức', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="company_name"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Mã đối tác', 'qlcv'); ?> <span class="text-danger">*</span></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_code"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Họ và tên', 'qlcv'); ?></div>
                                                <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="first_name" placeholder="<?php _e('Họ', 'qlcv'); ?>"></div>
                                                <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="last_name" placeholder="<?php _e('Tên', 'qlcv'); ?>"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45">Email <span class="text-danger">*</span></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Phân loại', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20">
                                                    <select class="form-control mb-20" name="type_of_client">
                                                        <option value="Private Firm">Private Firm</option>
                                                        <option value="Individual">Individual</option>
                                                        <option value="Law Firm">Law Firm</option>
                                                        <option value="IP Agent">IP Agent</option>
                                                        <option value="Consulting service">Consulting service</option>
                                                        <option value="Corporate service">Corporate service</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Số điện thoại', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Địa chỉ', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Quốc gia', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="country"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Ghi chú', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"></textarea></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Link file hồ sơ', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Nhúng link từ one drive', 'qlcv'); ?>" name="link_onedrive"></textarea></div>
                                                <div class="col-lg-3"></div>

                                                <?php
                                                wp_nonce_field('post_nonce', 'post_nonce_field');
                                                ?>
                                                <input type="hidden" name="role" value="partner">

                                                <div class="col-lg-3"></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Tạo mới', 'qlcv'); ?>"></div>


                                            </form>
                                        </div>
                                    </div>
                                    <div class="foreign_partner">
                                        <div class="col-12 mb-20">
                                            <h4><?php _e('Chọn đối tác nhận việc từ trong danh sách', 'qlcv'); ?></h4>
                                            <select class="form-control select2-tags mb-20" name="foreign_partner">
                                                <option value="">-- <?php _e('Chọn đối tác nhận việc', 'qlcv'); ?> --</option>
                                                <?php
                                                $args   = array(
                                                    'role'      => 'foreign_partner', /*subscriber, contributor, author*/
                                                );
                                                $query = get_users($args);

                                                if ($query) {
                                                    foreach ($query as $user) {
                                                        $ten_cong_ty    = get_field('ten_cong_ty', 'user_' . $user->ID);
                                                        $partner_code   = get_field('partner_code', 'user_' . $user->ID);
                                                        echo "<option value='" . $user->ID . "'>" . $partner_code . " - " . $ten_cong_ty . " (" . $user->user_email . ")</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-12 mb-20">
                                            <button class="button button-primary create_new_button" data-div="#create_foreign_partner"><span><i class="fa fa-user-plus"></i><?php _e('Tạo đối tác nước ngoài mới', 'qlcv'); ?></span></button>
                                            <div id="create_foreign_partner" style="display: none;">
                                                <form action="#" method="POST" class="row">
                                                    <div class="col-12 mb-20 notification">
                                                        <h4><?php _e('Nhập thông tin đối tác', 'qlcv'); ?></h4>
                                                    </div>
                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Tên công ty/tổ chức', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="company_name"></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Mã đối tác', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_code"></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Họ và tên', 'qlcv'); ?></div>
                                                    <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="first_name" placeholder="<?php _e('Họ', 'qlcv'); ?>"></div>
                                                    <div class="col-lg-3 col-12 mb-20"><input type="text" class="form-control" name="last_name" placeholder="<?php _e('Tên', 'qlcv'); ?>"></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45">Email</div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email"></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Phân loại', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20">
                                                        <select class="form-control mb-20" name="type_of_client">
                                                            <option value="Private Firm">Private Firm</option>
                                                            <option value="Individual">Individual</option>
                                                            <option value="Law Firm">Law Firm</option>
                                                            <option value="IP Agent">IP Agent</option>
                                                            <option value="Consulting service">Consulting service</option>
                                                            <option value="Corporate service">Corporate service</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Số điện thoại', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number"></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Địa chỉ', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address"></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Quốc gia', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="country"></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Ghi chú', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"></textarea></div>
                                                    <div class="col-lg-3"></div>

                                                    <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Link file hồ sơ', 'qlcv'); ?></div>
                                                    <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Nhúng link từ one drive', 'qlcv'); ?>" name="link_onedrive"></textarea></div>
                                                    <div class="col-lg-3"></div>

                                                    <?php
                                                    wp_nonce_field('post_nonce', 'post_nonce_field');
                                                    ?>
                                                    <input type="hidden" name="role" value="foreign_partner">

                                                    <div class="col-lg-3"></div>
                                                    <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Tạo mới', 'qlcv'); ?>"></div>

                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-20">
                                        <h4><?php _e('Chọn khách hàng từ trong danh sách', 'qlcv'); ?></h4>
                                        <select class="form-control select2-tags mb-20" name="customer">
                                            <option value="">-- <?php _e('Chọn khách hàng', 'qlcv'); ?> --</option>
                                            <?php
                                            $args   = array(
                                                'post_type'     => 'customer',
                                                'posts_per_page' => -1,
                                            );
                                            $query = new WP_Query($args);

                                            if ($query->have_posts()) {
                                                while ($query->have_posts()) {
                                                    $query->the_post();

                                                    $cty = get_field('ten_cong_ty');
                                                    $email = get_field('email');

                                                    echo "<option value='" . get_the_ID() . "'>" . $cty;
                                                    if ($email) {
                                                        echo " (" . $email . ")";
                                                    }
                                                    echo "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-20">
                                        <div id="customer_function">
                                            <button class="button button-primary create_customer"><span><i class="fa fa-user-plus"></i><?php _e('Tạo khách hàng mới', 'qlcv'); ?></span></button>
                                            <button class="button button-primary copy_customer"><span><i class="fa fa-user-plus"></i><?php _e('Copy dữ liệu đối tác', 'qlcv'); ?></span></button>
                                        </div>
                                        <div id="create_customer" style="display: none;">
                                            <form action="#" method="POST" class="row">
                                                <div class="col-12 mb-20 notification">
                                                    <h4><?php _e('Nhập thông tin khách hàng mới', 'qlcv'); ?></h4>
                                                </div>
                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Tên công ty/Tên khách', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="customer_name"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Số điện thoại', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="phone_number"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45">Email</div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="user_email"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Địa chỉ', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="address"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Quốc gia', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="text" class="form-control" name="country"></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Ghi chú', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Thông tin bổ sung', 'qlcv'); ?>" name="note"></textarea></div>
                                                <div class="col-lg-3"></div>

                                                <div class="col-lg-3 form_title text-left text-lg-right lh45"><?php _e('Link file hồ sơ', 'qlcv'); ?></div>
                                                <div class="col-lg-6 col-12 mb-20"><textarea class="form-control" placeholder="<?php _e('Nhúng link từ one drive', 'qlcv'); ?>" name="link_onedrive"></textarea></div>
                                                <div class="col-lg-3"></div>

                                                <?php
                                                wp_nonce_field('post_nonce', 'post_nonce_field');
                                                ?>

                                                <div class="col-lg-3"></div>
                                                <div class="col-lg-6 col-12 mb-20"><input type="submit" class="button button-primary" value="<?php _e('Tạo mới', 'qlcv'); ?>"></div>

                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="step-2">
                                <div class="row mbn-20">
                                    <form action="" method="POST" id="finance" class="row">
                                        <div class="col-lg-3 form_title text-left text-lg-right"><?php _e('Loại tiền', 'qlcv'); ?></div>
                                        <div class="col-lg-6 col-12 mb-20">
                                            <div class="form-group">
                                                <label class="inline"><input type="radio" name="currency" value="USD" checked>USD</label>
                                                <label class="inline"><input type="radio" name="currency" value="VND">VND</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-3"></div>

                                        <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Tổng số tiền', 'qlcv'); ?></div>
                                        <div class="col-lg-6 col-12 mb-20"><input type="number" placeholder="0" class="form-control" name="total_value"></div>
                                        <div class="col-lg-3"></div>

                                        <div class="col-lg-3 form_title lh45 text-left text-lg-right"><?php _e('Đã thanh toán', 'qlcv'); ?></div>
                                        <div class="col-lg-6 col-12 mb-20"><input type="number" placeholder="0" class="form-control" name="paid"></div>
                                        <div class="col-lg-3"></div>
                                    </form>
                                </div>
                            </div>
                            <div id="step-3">
                                <div class="row mbn-20">
                                    <div class="col-12 mb-20">
                                        <h4><?php _e('Chọn người quản lý', 'qlcv'); ?></h4>
                                        <select class="form-control select2-tags mb-20" name="manager">
                                            <?php
                                            $args   = array(
                                                'role'      => 'contributor', /*subscriber, contributor, author*/
                                            );
                                            $query = get_users($args);

                                            if ($query) {
                                                foreach ($query as $user) {
                                                    echo "<option value='" . $user->ID . "'>" . $user->display_name . " (" . $user->user_email . ")</option>";
                                                }
                                            }
                                            ?>
                                        </select>

                                        <h4 style="margin-top: 30px;"><?php _e('Chọn người thực hiện', 'qlcv'); ?></h4>
                                        <select class="form-control select2-tags mb-20" name="member">
                                            <?php
                                            $args   = array(
                                                'role__in'      => array('member', 'contributor'), /*subscriber, contributor, author*/
                                            );
                                            $query = get_users($args);

                                            if ($query) {
                                                foreach ($query as $user) {
                                                    echo "<option value='" . $user->ID . "'>" . $user->display_name . " (" . $user->user_email . ")</option>";
                                                }
                                            }
                                            ?>
                                        </select>

                                        <!-- Chọn người giám sát -->
                                        <h4 style="margin-top: 30px;"><?php _e('Chọn người giám sát', 'qlcv'); ?></h4>
                                        <select class="form-control select2-tags mb-20" multiple="" name="supervisor" >
                                            <?php
                                            $args   = array(
                                                'role__in'      => array('administrator', 'editor', 'contributor'), /*subscriber, contributor, author*/
                                            );
                                            $query = get_users($args);

                                            if ($query) {
                                                foreach ($query as $user) {
                                                    echo "<option value='" . $user->ID . "'>" . $user->display_name . " (" . $user->user_email . ")</option>";
                                                }
                                            }
                                            ?>
                                        </select>

                                        <?php
                                            $terms = get_terms(array(
                                                'taxonomy' => 'agency',
                                                'hide_empty' => false,
                                            ));
                                            if($terms){
                                                echo '<h4 style="margin-top: 30px;">' . __('Chọn chi nhánh thực hiện', 'qlcv') . '</h4>
                                                      <select class="form-control select2-tags mb-20" name="agency">';
                                                      
                                                foreach ($terms as $value) {
                                                    echo "<option value='" . $value->name . "'>" . $value->name . "</option>";
                                                }
                                                echo '</select>';
                                            }
                                        ?>
                                        
                                    </div>
                                    <div class="col-12 mb-20">
                                        <button class="button button-primary finish_newjob"><span><i class="fa fa-user-plus"></i><?php _e('Hoàn tất', 'qlcv'); ?></span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- Page Headings End -->

</div><!-- Content Body End -->

<?php
get_footer();
?>