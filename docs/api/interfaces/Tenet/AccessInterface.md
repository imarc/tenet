# AccessInterface



#### Namespace

`Tenet`


## Methods

### Instance Methods
<hr />

#### <span style="color:#3e6a6e;">listAccessibleFields()</span>

List the fields allowed to be accessed

###### Returns

<dl>
	
		<dt>
			array
		</dt>
		<dd>
			An array of the fields allowed to be accessed
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">generateGetterCallable()</span>

Generate a callable capable of getting a field value

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$field
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The name of the field to generate the getter for
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			Callable
		</dt>
		<dd>
			A getter callable
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">generateSetterCallable()</span>

Generate a callable capable of setting a field value

###### Parameters

<table>
	<thead>
		<th>Name</th>
		<th>Type(s)</th>
		<th>Description</th>
	</thead>
	<tbody>
			
		<tr>
			<td>
				$field
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The name of the field to generate the setter for
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			Callable
		</dt>
		<dd>
			A setter callable
		</dd>
	
</dl>




