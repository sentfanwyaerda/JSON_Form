<?php 
class JSON_Form{
	var $db = array();
	function load($json, $load_file=FALSE){
		if($load_file !== FALSE && file_exists($json)){ $json = file_get_contents($json); }
		$this->db = json_decode($json, TRUE);
	}
	function export(){
		$json = json_encode($this->db);
		return $json;
	}
	
	/* used with Morpheus */
	function generate_flags($language=NULL){
		$flags = array();
		foreach($this->db["items"] as $i=>$item){
			//$flags['form.'.$item['name'].'.type'] = $item['type'];
			foreach($item as $n=>$v){
				$flags['form.'.$item['name'].'.'.$n] = $v /* $item[$n] */;
			}
		}
		return $flags;
	}
	function get_language($language=NULL){
		if($language === NULL){
			if(isset($this->db["default-language"])){$language = $this->db["default-language"]; }
			else { $language = 'en'; }
		}
		return $language;
	}
	function get_text($find=NULL, $tag=NULL, $language=NULL, $defaultvalue=NULL){
		if(!is_array($find)){ $find = array("name" => $find); }
		foreach($this->db["text"] as $l=>$set){
			if($l == $this->get_language($language) && is_array($set)){
				foreach($set as $i=>$text){
					if(JSON_Form::identify($text, $find)){
						return ($tag === NULL ? $text : (isset($text[$tag]) ? $text[$tag] : $defaultvalue) );
					}
				}
			}
		}
		return ($tag === NULL ? array() : $defaultvalue);
	}
	function identify($set=array(), $criteria=array()){
		$bool = TRUE;
		foreach($criteria as $n=>$v){
			$bool = ($bool && (isset($set[$n]) && $set[$n] == $v) );
		}
		return $bool;
	}
	function generate_html($language=NULL){
		$str = NULL;
		$str .= '<form';
		if(isset($this->db["form"])){ $str .= ' id="'.$this->db["form"].'"'; }
		if(isset($this->db["action"])){ $str .= ' action="'.$this->db["action"].'"'; }
		if(isset($this->db["method"])){ $str .= ' method="'.strtoupper($this->db["method"]).'"'; }
		if(isset($this->db["class"])){ $str .= ' class="'.strtoupper($this->db["class"]).'"'; }
		$str .= '>'."\n";
		
		
		foreach($this->db["datalist"] as $i=>$datalist){
			$str .= '<datalist id="'.$datalist['id'].'">';
			foreach($datalist['options'] as $j=>$option){
				$str .= '<option value="'.$option.'" />';
			}
			$str .= '</datalist>'."\n";
			$str .= '<script type="text/javascript">'.$datalist['id'].'Tags = '.json_encode($datalist['options']).';</script>'."\n";
		}
		$str .= '<script type="text/javascript">$.datepicker.setDefaults($.datepicker.regional[\''.$this->get_language().'\']);</script>';
		
		$str .= '<fieldset class="main'.(isset($this->db["form"]) ? ' '.$this->db["form"] : NULL).'">'."\n";
		$str .= $this->_generate_html_item($this->db["items"], $language);
		$str .= '</fieldset>'."\n";
		
		$str .= '</form>';
		/*fix*/ $str = preg_replace("#\{\|([^\}]+)\}#i", "\\1", $str);
		/*fix*/ $str = preg_replace("#<!--\s*divider\s*-->#i", '</fieldset>'."\n".'<fieldset class="main'.(isset($this->db["form"]) ? ' '.$this->db["form"] : NULL).'">'."\n", $str);
		return $str;
	}
	function _generate_html_item($items=array(), $language=NULL, $parentname=FALSE){
		$str = NULL;
		foreach($items as $i=>$item){
			if(!is_array($item)){ $str .= $item."\n"; }
			elseif(isset($item['type']) && strtolower($item['type']) == 'set'){
				$first = array_shift(array_keys($item));
				$id = array($first => $item[$first]);
				$subjectname = (isset($item['name']) ? ($parentname !== FALSE ? $parentname.'['.$item['name'].']' : $item['name']) : FALSE);
				$str .= '<div class="set '.(isset($item['name']) ? 'f-'.$item['name'] : NULL).'">';
				if(isset($item['prefix'])){ $str .= '<span class="prefix">'.$this->get_text($id, 'prefix', $language, $item['prefix']).'</span>'; }
				if(isset($item['items']) && is_array($item['items'])){ $str .= $this->_generate_html_item($item['items'], $language, $subjectname); }
				if(isset($item['postfix'])){ $str .= '<span class="postfix">'.$this->get_text($id, 'postfix', $language, $item['postfix']).'</span>'; }
				$str .= '</div>'."\n";
			}
			else{
				$first = array_shift(array_keys($item));
				$id = array($first => $item[$first]);
				$subjectname = (isset($item['name']) ? ($parentname !== FALSE ? $parentname.'['.$item['name'].']' : $item['name']) : FALSE);
				
				$str .= '<span class="item '.(isset($item['name']) ? 'f-'.$item['name'] : NULL).' '.(isset($item['type']) ? 'input-'.$item['type'] : NULL).'"';
				$str .= '>';
				
				if(isset($item['type'])){switch(strtolower($item['type'])){
					case 'tag-it':
						if(!isset($item['id'])){ $item['id'] = $item['name']; }
						$str .= '<script type="text/javascript"> $(document).ready(function() { $("#'.$item['id'].'").tagit({availableTags: '.$item['list'].'Tags}); });</script>';
						unset($item['type']);
						break;
					case 'date':
						if(!isset($item['id'])){ $item['id'] = $item['name']; }
						$datepicker = array(); //array('numberOfMonths'=>1, 'showButtonPanel'=>false, 'changeMonth'=>true, 'changeYear'=>true, 'showOtherMonths'=>  true, 'selectOtherMonths'=> true); /*set with the default values*/
						foreach(array_merge(array_keys($datepicker), array('altField','altFormat','appendText','autoSize','beforeShow','beforeShowDay','buttonImage','buttonImageOnly','buttonText','calculateWeek','changeMonth','changeYear','closeText','constrainInput','currentText','dateFormat','dayNames','dayNamesMin','dayNamesShort','defaultDate','duration','firstDay','gotoCurrent','hideIfNoPrevNext','isRTL','maxDate','minDate','monthNames','monthNamesShort','navigationAsDateFormat','nextText','numberOfMonths','onChangeMonthYear','onClose','onSelect','prevText','selectOtherMonths','shortYearCutoff','showAnim','showButtonPanel','showCurrentAtPos','showMonthAfterYear','showOn','showOptions','showOtherMonths','showWeek','stepMonths','weekHeader','yearRange','yearSuffix')) as $df){
							if(isset($item[$df])){ $datepicker[$df] = $item[$df]; }
						}
						$str .= '<script type="text/javascript">$(function () { $("#'.$item['id'].'").datepicker('.json_encode($datepicker).'); });</script>';
						break;
					case 'checkbox': if(!isset($item['id'])){ $item['id'] = $item['name']; } break;
					case 'radio': break;
					case 'select': break;
				}}
				
				if(isset($item['prefix'])){ $str .= '<span class="prefix">'.$this->get_text($id, 'prefix', $language, $item['prefix']).'</span>'; }
				
				if(isset($item['type']) && strtolower($item['type']) == 'select'){
					$str .= '<select ';
					if(isset($item['name'])){ $str .= ' name="'.$subjectname.'"'; }
					foreach(array('id','class','style','required','disabled','readonly') as $tag){
						if(isset($item[$tag]) || $this->get_text($id, $tag, $language) != NULL){ $str .= ' '.$tag.'="'.$this->get_text($id, $tag, $language, $item[$tag]).'"'; }
					}
					$str .= '>';
					$oi = 0;
					if(isset($item['options']) && is_array($item['options'])){ foreach($item['options'] as $o=>$v){
						$str .= '<option value="'.($o !== $oi ? $o : $v).'"'.' {'.$subjectname.'=='.($o !== $oi ? $o : $v).'?selected=true:}'.( /*$o != $oi && */ $this->get_text(array('option'=>$subjectname.'='.($o !== $oi ? $o : $v)), 'description', $language, $v) != NULL ? '>'.$this->get_text(array('option'=>$subjectname.'='.($o !== $oi ? $o : $v)), 'description', $language, $v).'</option>' : '/>'); $oi++;
					}}
					$str .= '</select>';
				}
				elseif(isset($item['type']) && strtolower($item['type']) == 'radio'){
					$oi = 0;
					foreach($item['items'] as $o=>$v){
						$str .= '<input';
						if(isset($item['name'])){ $str .= ' name="'.$subjectname.'"'; }
						foreach(array('type','class','style','required','disabled','readonly') as $tag){
							if(isset($item[$tag]) || $this->get_text($id, $tag, $language) != NULL){ $str .= ' '.$tag.'="'.$this->get_text($id, $tag, $language, $item[$tag]).'"'; }
						}
						$str .= ' id="'.$subjectname.'-'.($o !== $oi ? $o : $v).'" /><label for="'.$subjectname.'-'.($o !== $oi ? $o : $v).'">'.$this->get_text(array('option' => $subjectname.'-'.($o !== $oi ? $o : $v)), 'value', $language, $v).'</label>';
						$oi++;
					}
				}
				else{
					$str .= '<input';
					if(isset($item['name'])){ $str .= ' name="'.$subjectname.'"'; }
					foreach(array('id','type','class','style','placeholder','list','required','disabled','readonly','autocomplete','pattern','maxlength') as $tag){
						if(isset($item[$tag]) || $this->get_text($id, $tag, $language) != NULL){ $str .= ' '.$tag.'="'.$this->get_text($id, $tag, $language, $item[$tag]).'"'; }
					}
					if((isset($item['name']) || isset($item['id'])) && !(isset($item['value']) && $item['value'] == FALSE)){ $str .= ' value="{'.$subjectname.'|'.$this->get_text($id, 'value', $language, $item['value']).'}"'; }
					$str .= ' />';
				}
				
				if(isset($item['label'])){ $str .= '<label class="postfix"'.(isset($item['id']) ? ' for="'.$item['id'].'"' : NULL).'>'.$this->get_text($id, 'label', $language, $item['label']).'</label>'; }

				if(isset($item['postfix'])){ $str .= '<span class="postfix">'.$this->get_text($id, 'postfix', $language, $item['postfix']).'</span>'; }
				$str .= "</span>\n";
			}
		}
		return $str;
	}
}
?>