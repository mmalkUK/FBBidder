<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MHelperHTML
 *
 * @author Marcin
 */
class MHelperHTML {
	static function tableDT($data, $actions = null){
		$html = '<div class="row"><div class="col-xs-12">';
		$html .= '<table class="table table-striped m-b-none" id="dataTableM" data-ride="datatables">';
		$html .= '<thead><tr>';
		foreach($data as $key => $value){
			$html .= "<th>$value</th>";
		}
		if($actions != null){
			foreach($actions as $key => $value){
				$html .= "<th>$key</th>";
			}			
		}
		$html .= '</tr></thead><tbody></table></div></div>';
		return $html;
	}

	static function editForm($data, $dbData, $cmd, $id){
		$html = '<div class="row"><div class="col-xs-12"><form role="form" method="POST" action="edit?cmd=' . $cmd . '&id=' . $id . '">' . "\n";
		foreach($data as $key => $value){
			
			if($value[1] != 'checkbox'){
				$html .= '<div class="form-group">' . "\n";
				$html .= '<label for="' . $key . '">' . $value[0] . '</label>' . "\n";
				$html .= '<input type="' . $value[1] . '" class="form-control" name="' . $key . '" id="' . $key . '" placeholder="' . $dbData[$value[0]] . '" value="' . $dbData[$value] . '">' . "\n";
				$html .= '</div>' . "\n";
			}else{
				$html .= '<div class="checkbox">' . "\n";
				$html .= '<label>' . "\n";
				$html .= '<input type="checkbox" id="' . $key . '" name="' . $key . '"';
				if($dbData[$key] == '1'){
					$html .= ' checked ';
				}
				$html .= 'value="' . $dbData[$key] . '"';
				$html .= '>' . $value[0] . "\n";
				$html .- '</label>' . "\n";
				$html .= '</div>' . "\n";
			}
		}
		$html .= '<div class="form-group">' . "\n";
		$html .= '<input type="hidden" class="form-control" id="id" name="id" value="' . $id . '">' . "\n";
		$html .= '</div>' . "\n";            
		
		$html .= '<button type="submit" class="btn btn-default">Save</button></form></div></div>' . "\n";
		return $html;
	}
	
	static function createForm($data, $cmd, $by_id){
		$html = '<div class="row"><div class="col-xs-12"><form role="form" method="POST" action="create?cmd=' . $cmd . '">' . "\n";
		foreach($data as $key => $value){
			$html .= '<div class="form-group">' . "\n";
			if($value[1] != 'hidden'){
				$html .= '<label for="' . $key . '">' . $value[0] . '</label>' . "\n";
			}
			$html .= '<input type="' . $value[1] . '" class="form-control" name="' . $key . '" id="' . $key . '" placeholder="' . $value[0] . '"';
			if($value[2]){
				$html .= ' aria-required="true" required=""';
			}
			$html .= '>' . "\n";
			$html .= '</div>' . "\n";
			
			if($value[3] == 'match'){
				$html .= '<div class="form-group">' . "\n";
				$html .= '<label for="' . $key . '">' . $value[0] . '</label>' . "\n";
				$html .= '<input type="' . $value[1] . '" class="form-control" name="' . $key . '2" id="' . $key . '2" placeholder="' . $value[0] . '"';
				if($value[2]){
					$html .= ' aria-required="true" required=""';
				}
				$html .= '>' . "\n";
				$html .= '</div>' . "\n";                    
			}

		}
		
		$html .= '<div class="form-group">' . "\n";
		$html .= '<input type="hidden" class="form-control" id="by_id" name="by_id" value="' . $by_id . '">' . "\n";
		$html .= '</div>' . "\n";            
		$html .= '<button type="submit" class="btn btn-default">Save</button></form></div></div>' . "\n";
		return $html;
	}    
	
	static function message($triggerButtonId, $message, $title, $cmd, $buttonIDorClass = '#'){
		$html = "\n<!-- messages $triggerButtonId-->\n";
		$html .= "<script>\n";
		$html .= "	$('" . $buttonIDorClass . $triggerButtonId . "').click(function(){ \n";
		$html .= "		$('#" . $triggerButtonId . "Id').val($(this).val());\n";
		$html .= "		$('#message$triggerButtonId').modal({show: true});\n";
		$html .= "	});";
		$html .= "</script>\n";
		$html .= '<div class="modal fade" id="message' . $triggerButtonId . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' . "\n";
		$html .= '	<div class="modal-dialog">' . "\n";
		$html .= '		<div class="modal-content">' . "\n";
		$html .= '			<div class="modal-header">' . "\n";
		$html .= '				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' . "\n";
		$html .= '				<h4 class="modal-title" id="myModalLabel">' . $title . '</h4>' . "\n";
		$html .= '			</div>' . "\n";
		$html .= '			<div class="modal-body">' . "\n";
		$html .= $message;
		$html .= '			</div>' . "\n";
		$html .= '			<div class="modal-footer">' . "\n";
		$html .= '				<form id="' . $triggerButtonId . 'Confirmation" style="display: inline;" action="" method="POST">' . "\n";
		$html .= '					<input type="hidden" name="cmd" id="' . $triggerButtonId . 'Cmd" value="' . $cmd . '">'  . "\n";
		$html .= '					<input type="hidden" name="Id" id="' . $triggerButtonId . 'Id" value="">'  . "\n";
		$html .= '					<button type="submit" class="btn btn-default">Confirm</button>'  . "\n";
		$html .= '				</form>	' . "\n";
		$html .= '				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\n";
		$html .= '			</div>' . "\n";
		$html .= '		</div>' . "\n";
		$html .= '	</div>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '<!-- messages end ' . $triggerButtonId . '-->'  . "\n";			
		
		return $html;
	}
	
}





