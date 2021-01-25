<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Statistics extends MY_Controller {

	public function __construct()
    {
		parent::__construct();
		$this->load->model('Statistics_Model', 'statistics');
    }

    public function test(){
        // echo json_encode(["status"=>true, "message"=>'hola mundo']);
        return $this->output_json(404,'The title is necessary');
    }

    /**
     * New Chart
     * --------------------------
     * @param: $title
     * @param: $description
     * @param: $month
     * @param: $year
     * @param: $file
     * @param: $image
     * --------------------------
     * @method : POST
     * @link : /newchart/
     */
    public function newchart()
	{   
        #validating input data
        if ( !$this->input->post('title') )        return $this->output_json(400,'The title is necessary');
        if ( !$this->input->post('description') )  return $this->output_json(400,'The description is necessary');
        if ( !$this->input->post('month') )        return $this->output_json(400,'The month is necessary');
        if ( !$this->input->post('year') )         return $this->output_json(400,'The year is necessary');
        // if ( !isset($_FILES['file']) )             return $this->output_json(400,'The file is necessary');
        if ( !isset($_FILES['image']) )            return $this->output_json(400,'The image is necessary');
        if ( !$_FILES['image']['tmp_name'] )       return $this->output_json(400,'I dont select any image');
        // if ( !$_FILES['file']['tmp_name'] )        return $this->output_json(400,'I dont select any file');

        #save img
        $path = IMG;
        $img = $_FILES['image']['tmp_name'];
        $img_name = $this->clearName(explode('.',$_FILES['image']['name'])[0]).'.jpg';
        $this->fileUpload($img, $img_name, $path);
        $target_img = UPLOAD . IMG . $img_name;

        #save file
        /* $path = PDF;
        $file = $_FILES['file']['tmp_name'];
        $file_name = $this->clearName(explode('.',$_FILES['file']['name'])[0]).'.pdf';
        $this->fileUpload($file, $file_name, $path);
        $target_file = UPLOAD . PDF . $file_name; */

        $this->data[0] = [
            'title'       => $this->input->post('title'),
            'description' => $this->input->post('description'),
            'month'       => $this->input->post('month'),
            'year'        => $this->input->post('year'),
            'file'        => '',//$target_file,
            'image'       => $target_img,
        ];

        #an error occurred 
        if( !$this->statistics->setdata( $this->data[0] , 'statistics' ) ) return $this->output_json(200,'an error occurred while inserting the data');

        return $this->output_json(200,'query successfully', $this->data);
        

    }

    public function editchart(){
        #validating input data
        if ( !$this->input->post('id') )           return $this->output_json(400,'The id is necessary');
        if ( !$this->input->post('title') )        return $this->output_json(400,'The title is necessary');
        if ( !$this->input->post('description') )  return $this->output_json(400,'The description is necessary');
        if ( !$this->input->post('month') )        return $this->output_json(400,'The month is necessary');
        if ( !$this->input->post('year') )         return $this->output_json(400,'The year is necessary');
        // if ( !isset($_FILES['file']) )             return $this->output_json(400,'The file is necessary');
        if ( !isset($_FILES['image']) )            return $this->output_json(400,'The image is necessary');
        if ( !$_FILES['image']['tmp_name'] )       return $this->output_json(400,'I dont select any image');
        // if ( !$_FILES['file']['tmp_name'] )        return $this->output_json(400,'I dont select any file');

        #save img
        $path = IMG;
        $img = $_FILES['image']['tmp_name'];
        $img_name = $this->clearName(explode('.',$_FILES['image']['name'])[0]).'.jpg';
        $this->fileUpload($img, $img_name, $path);
        $target_img = UPLOAD . IMG . $img_name;

        #save file
        /* $path = PDF;
        $file = $_FILES['file']['tmp_name'];
        $file_name = $this->clearName(explode('.',$_FILES['file']['name'])[0]).'.pdf';
        $this->fileUpload($file, $file_name, $path);
        $target_file = UPLOAD . PDF . $file_name; */

        $where = ['id' => $this->input->post('id')];
        $this->data[0] = [
            'title'       => $this->input->post('title'),
            'description' => $this->input->post('description'),
            'month'       => $this->input->post('month'),
            'year'        => $this->input->post('year'),
            'file'        => '',//$target_file, 
            'image'       => $target_img,
        ];

        #an error occurred 
        if( !$this->statistics->upddata( $this->data[0] , $where, 'statistics') ) return $this->output_json(200,'an error occurred while updating the data');

        return $this->output_json(200,'query successfully', $this->data);
    }

    public function deletechart(){
        #validating input data
        if ( !$this->input->post('id') )           return $this->output_json(400,'The id is necessary');
        $data = ['id' => $this->input->post('id')];

        #an error occurred 
        if( !$this->statistics->deldata( $data , 'statistics' ) ) return $this->output_json(200,'an error occurred while delete the data');
        return $this->output_json(200,'query successfully');
    }

    public function getchart(){
        $notes_quanty = 10;
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');

        $for_page   = $limit ? (int) $limit : $notes_quanty;
        $offset     = $page  ? $for_page * ($page - 1) : 0;
        $page = $page ? (int) $page : 1 ;
        
        $select = '*';
        $table = 'statistics';
        #an error occurred 
        $this->data = $this->statistics->getdata($select, $table, [], 'id', $for_page, $offset);

        $pages = ($this->data['countAll'] % $for_page ) ?   (int)($this->data['countAll'] / $for_page) + 1 : (int)$this->data['countAll'] / $for_page  ; 
        $this->data['page'] = $page;
        $this->data['pages'] = $pages;
        if( !$this->data ) return $this->output_json(200,'an error occurred while get the dataa');
        return $this->output_json(200,'query successfully', $this->data);
    }

    public function newbulletin(){
        #validating input data
        if ( !$this->input->post('title') )        return $this->output_json(400,'The title is necessary');
        if ( !$this->input->post('month') )        return $this->output_json(400,'The month is necessary');
        if ( !$this->input->post('year') )         return $this->output_json(400,'The year is necessary');
        if ( !isset($_FILES['file']) )             return $this->output_json(400,'The file is necessary');
        if ( !$_FILES['file']['tmp_name'] )        return $this->output_json(400,'I dont select any file');

        #save file
        $path = PDF;
        $file = $_FILES['file']['tmp_name'];
        $file_name = $this->clearName(explode('.',$_FILES['file']['name'])[0]).'.pdf';
        $this->fileUpload($file, $file_name, $path);
        $target_file = UPLOAD . PDF . $file_name;

        $this->data[0] = [
            'title'       => $this->input->post('title'),
            'month'       => $this->input->post('month'),
            'year'        => $this->input->post('year'),
            'file'        => $target_file,
        ];

        #an error occurred 
        if( !$this->statistics->setdata( $this->data[0], 'bulletin' ) ) return $this->output_json(200,'an error occurred while inserting the data');

        return $this->output_json(200,'query successfully', $this->data);
    }

    public function editbulletin(){
        #validating input data
        if ( !$this->input->post('title') )        return $this->output_json(400,'The title is necessary');
        if ( !$this->input->post('month') )        return $this->output_json(400,'The month is necessary');
        if ( !$this->input->post('year') )         return $this->output_json(400,'The year is necessary');
        if ( !isset($_FILES['file']) )             return $this->output_json(400,'The file is necessary');
        if ( !$_FILES['file']['tmp_name'] )        return $this->output_json(400,'I dont select any file');

        #save file
        $path = PDF;
        $file = $_FILES['file']['tmp_name'];
        $file_name = $this->clearName(explode('.',$_FILES['file']['name'])[0]).'.pdf';
        $this->fileUpload($file, $file_name, $path);
        $target_file = UPLOAD . PDF . $file_name;

        $where = ['id' => $this->input->post('id')];
        $this->data[0] = [
            'title'       => $this->input->post('title'),
            'month'       => $this->input->post('month'),
            'year'        => $this->input->post('year'),
            'file'        => $target_file,
        ];

        #an error occurred 
        if( !$this->statistics->upddata( $this->data[0], $where, 'bulletin' ) ) return $this->output_json(200,'an error occurred while updating the data');

        return $this->output_json(200,'query successfully', $this->data);
    }

    public function deletebulletin(){
        #validating input data
        if ( !$this->input->post('id') ) return $this->output_json(400,'The id is necessary');
        $data = ['id' => $this->input->post('id')];

        #an error occurred 
        if( !$this->statistics->deldata( $data , 'bulletin' ) ) return $this->output_json(200,'an error occurred while delete the data');
        return $this->output_json(200,'query successfully');
    }

    public function getbulletin(){
        // $notes_quanty = 10;
        // $page = $this->input->get('page');
        // $limit = $this->input->get('limit');

        // $for_page   = $limit ? (int) $limit : $notes_quanty;
        // $offset     = $page  ? $for_page * ($page - 1) : 0;
        // $page = $page ? (int) $page : 1 ;

        $select = '*';
        $table = 'bulletin';
        #an error occurred 
        $this->data = $this->statistics->getdata($select, $table, [], 'id');

        /* $pages = ($this->data['countAll'] % $for_page ) ?   (int)($this->data['countAll'] / $for_page) + 1 : (int)$this->data['countAll'] / $for_page  ; 
        $this->data['page'] = $page;
        $this->data['pages'] = $pages; */
        if( !$this->data ) return $this->output_json(400,'an error occurred while get the data');
        return $this->output_json(200,'query successfully', $this->data);
    }
    
    public function newIndicador(){
        #validating input data
        if ( !$this->input->post('title') )        return $this->output_json(400,'The title is necessary');
        if ( !$this->input->post('description') )  return $this->output_json(400,'The description is necessary');
        if ( !$this->input->post('percentage') )   return $this->output_json(400,'The percentage is necessary');
        if ( !$this->input->post('type') )         return $this->output_json(400,'The type is necessary');


        $this->data = [
            'title'             => $this->input->post('title'),
            'description'       => $this->input->post('description'),
            'percentage'        => (int)$this->input->post('percentage'),
            'type'              => strtolower($this->input->post('type')),
        ];

        #an error occurred 
        if( !$this->statistics->setdata( $this->data, 'indicadores' ) ) return $this->output_json(200,'an error occurred while inserting the data');

        return $this->output_json(200,'query successfully', $this->data);
    }

    public function editIndicador(){

        #validating input data
        if ( !$this->input->post('title') )        return $this->output_json(400,'The title is necessary');
        if ( !$this->input->post('description') )  return $this->output_json(400,'The description is necessary');
        if ( !$this->input->post('percentage') )   return $this->output_json(400,'The percentage is necessary');
        if ( !$this->input->post('type') )         return $this->output_json(400,'The type is necessary');

    
        
        $where = ['id' => $this->input->post('id')];
        $this->data = [
            'title'             => $this->input->post('title'),
            'description'       => $this->input->post('description'),
            'percentage'        => $this->input->post('percentage'),
            'type'              => strtolower($this->input->post('type')),
        ];

        #an error occurred 
        if( !$this->statistics->upddata( $this->data , $where, 'indicadores' ) ) return $this->output_json(200,'an error occurred while updating the data');

        return $this->output_json(200,'query successfully', $this->data);
    }

    public function deleteIndicador(){
        #validating input data
        if ( !$this->input->post('id') )           return $this->output_json(400,'The id is necessary');
        $data = ['id' => $this->input->post('id')];

        #an error occurred 
        if( !$this->statistics->deldata( $data , 'indicadores' ) ) return $this->output_json(200,'an error occurred while delete the data');
        return $this->output_json(200,'query successfully');
    }

    public function getIndicador(){
        $notes_quanty = 10;
        $page = $this->input->get('page');
        $limit = $this->input->get('limit');

        $for_page   = $limit ? (int) $limit : $notes_quanty;
        $offset     = $page  ? $for_page * ($page - 1) : 0;
        $page = $page ? (int) $page : 1 ;

        $select = '*';
        $table = 'indicadores';
        #an error occurred 
        $this->data = $this->statistics->getdata($select, $table, [], 'id', $for_page, $offset);

        $pages = ($this->data['countAll'] % $for_page ) ?   (int)($this->data['countAll'] / $for_page) + 1 : (int)$this->data['countAll'] / $for_page  ; 
        $this->data['page'] = $page;
        $this->data['pages'] = $pages;
        if( !$this->data ) return $this->output_json(200,'an error occurred while get the dataa');
        return $this->output_json(200,'query successfully', $this->data);
    }
}