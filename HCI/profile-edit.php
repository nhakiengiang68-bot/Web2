<?php include "includes/header.php"; ?>

<?php include "includes/sidebar.php"; ?>

<?php include "includes/topnav.php"; ?>

<?php include "includes/database.php"; ?>

      <!-- Page Content  -->
      <div id="content-page" class="content-page">
         <div class="container-fluid">
            <div class="row">
               <div class="col-lg-12">
                  <div class="iq-edit-list-data">
                     <div class="tab-content">
                        <div class="tab-pane fade active show" id="personal-information" role="tabpanel">
                           <div class="iq-card">
                              <div class="iq-card-header d-flex justify-content-between">
                                 <div class="iq-header-title">
                                    <h4 class="card-title">Thông tin cá nhân</h4>
                                 </div>
                              </div>
                              <div class="iq-card-body">
                                 <form>
                                    <div class="form-group row align-items-center">
                                       <div class="col-md-12">
                                          <div class="profile-img-edit">
                                             <img class="profile-pic" src="images/user/1.jpg" alt="profile-pic">
                                             <div class="p-image">
                                                <i class="ri-pencil-line upload-button"></i>
                                                <input class="file-upload" type="file" accept="image/*"/>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class=" row align-items-center">
                                       <div class="form-group col-sm-6">
                                          <label for="fname">Họ:</label>
                                          <input type="text" class="form-control" id="fname" value="Đào Thiện">
                                       </div>
                                       <div class="form-group col-sm-6">
                                          <label for="lname">Tên:</label>
                                          <input type="text" class="form-control" id="lname" value="Phát">
                                       </div>
                                       <div class="form-group col-sm-6">
                                          <label class="d-block">Giới tính:</label>
                                          <div class="custom-control custom-radio custom-control-inline">
                                             <input type="radio" id="customRadio6" name="customRadio1" class="custom-control-input" checked="">
                                             <label class="custom-control-label" for="customRadio6"> Nam </label>
                                          </div>
                                          <div class="custom-control custom-radio custom-control-inline">
                                             <input type="radio" id="customRadio7" name="customRadio1" class="custom-control-input">
                                             <label class="custom-control-label" for="customRadio7"> Nữ </label>
                                          </div>
                                       </div>
                                       <div class="form-group col-sm-6">
                                          <label for="dob">Ngày sinh:</label>
                                          <input type="date" class="form-control" id="dob" value="2006-10-04">
                                       </div>
                                       <div class="form-group col-sm-6">
                                          <label>Quốc gia:</label>
                                          <select class="form-control" id="exampleFormControlSelect3">
                                             <option>Laos</option>
                                             <option>China</option>
                                             <option selected="">Việt Nam</option>
                                             <option>Indo</option>
                                             <option>USA</option>
                                          </select>
                                       </div>
                                       <div class="form-group col-sm-6">
                                          <label>Tỉnh/Thành phố:</label>
                                          <select class="form-control" id="exampleFormControlSelect4">
                                             <option></option>
                                             <option>Hà Nội</option>
                                             <option selected="">Hồ Chí Minh</option>
                                             <option>Hồ Chí Minh</option>
                                             <option>Buôn Ma Thuột</option>
                                          </select>
                                       </div>
                                       <div class="form-group col-sm-12">
                                          <label>Địa chỉ:</label>
                                          <textarea class="form-control" name="address" rows="5" style="line-height: 22px;">
                                             10/41A Âu Dương Lân
                                             Quận 8, Hồ Chí Minh
                                             Việt Nam
                                          </textarea>
                                       </div>
                                    </div>
                                    <a href="profile.php" class="btn btn-primary mr-2">Lưu</a>
                                    <a href="profile.php" class="btn btn-danger mr-2">Hủy bỏ</a>
                                 </form>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

<?php include "includes/footer.php"; ?>