<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
  // method untuk melakukan form validation agar bisa digunakan ketika mengakses auth
  public function __construct()
  {
    // untuk memanggil method constructor yang ada di CI_CONTROLLER
    parent::__construct();
    $this->load->library('form_validation');
  }
  // method dibawah untuk menampilkan login page
  public function index()
  {
    $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
    $this->form_validation->set_rules('password', 'Password', 'trim|required');
    if ($this->form_validation->run() == false) {
      $data["title"] = "Login Page";
      $this->load->view('templates/auth_header', $data);
      $this->load->view('auth/login');
      $this->load->view('templates/auth_footer');
    } else {
      // validasi succes
      $this->_login();
    }
  }

  private function _login()
  {
    $email = $this->input->post('email');
    $password = $this->input->post('password');

    // panggil table user  
    $user = $this->db->get_where('user', ['email' => $email])->row_array();
    // pengecekkan jika ada user nya ada
    if ($user) {
      // user nya aktif
      if ($user['is_active'] == 1) {
        // cek password
        if (password_verify($password, $user['password'])) {
          // dapatkan data email dan role nya apa
          $data = [
            'email' => $user['email'],
            'role_id' => $user['role_id']
          ];
          $this->session->set_flashdata($data);
          redirect('user');
        } else {
          $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">password anda salah</div>');
          redirect('auth');
        }
      } else {
        $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email anda tidak diaktivasi</div>');
        redirect('auth');
      }
    } else {
      // jika user tidak ada,, kasih pesan error
      $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Email anda tidak terdaftar</div>');
      // akan diarahkan ke halaman yang sama
      redirect('auth');
    }
  }

  public function registration()
  {
    // form validation untuk mengecek tiap2 element inpput yang ada di form registration
    $this->form_validation->set_rules('name', 'Name', 'required|trim');
    $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]');
    $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
      'matches' => 'Password doesnt matches!',
      'min_length' => 'Password too short!'
    ]);
    $this->form_validation->set_rules('password2', 'Password', 'required|trim|min_length[3]|matches[password1]');
    // kondisi mengecek jika user gagal maka akan diarahakn ke register
    if ($this->form_validation->run() == false) {
      $data["title"] = "User Registration";
      $this->load->view('templates/auth_header', $data);
      $this->load->view('auth/registration');
      $this->load->view('templates/auth_footer');
    } else {
      // untuk menyiman sebuah array yang memiliki value yang didapat dari registration.php
      $data = [
        // html specialchars untuk mengsanitasi input dan menghindari cross side scripting
        'name' => htmlspecialchars($this->input->post('name', true)),
        'email' => htmlspecialchars($this->input->post('email', true)),
        'image' => 'default.jpg',
        'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
        'role_id' => 2,
        'is_active' => 1,
        'date_created' => time()
      ];
      // method yang memasukkan data diatas menjadi isi
      $this->db->insert('user', $data);
      // memberikan sebuah alert bahwa registrasi berhasil
      $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Anda telah berhasil membuat account</div>');
      // memindahkan ke halaman login
      redirect('auth');
    }
  }
}
