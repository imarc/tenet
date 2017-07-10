# Accessor
## Doctrine Object Accessor


#### Namespace

`Tenet`

#### Imports

<table>

	<tr>
		<th>Alias</th>
		<th>Namespace / Target</th>
	</tr>
	
	<tr>
		<td>ObjectManager</td>
		<td>Doctrine\Common\Persistence\ObjectManager</td>
	</tr>
	
	<tr>
		<td>ClassMetadata</td>
		<td>Doctrine\ORM\Mapping\ClassMetadata</td>
	</tr>
	
</table>

#### Authors

<table>
	<thead>
		<th>Name</th>
		<th>Handle</th>
		<th>Email</th>
	</thead>
	<tbody>
	
		<tr>
			<td>
				Jeff Turcotte
			</td>
			<td>
				
			</td>
			<td>
				jeff@imarc.net
			</td>
		</tr>
	
	</tbody>
</table>

## Properties

### Instance Properties
#### <span style="color:#6a6e3d;">$filters</span>

#### <span style="color:#6a6e3d;">$registry</span>




## Methods

### Instance Methods
<hr />

#### <span style="color:#3e6a6e;">__construct()</span>

Constructor

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
				$registry
			</td>
			<td>
									Registry				
			</td>
			<td>
				An object/entity manager registry
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			void
		</dt>
		<dd>
			Provides no return value.
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">addTypeFilter()</span>

Register an accessor filter allowing for value manipulating on setting and getting

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
				$type
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The ObjectManager field type to associate the filter with
			</td>
		</tr>
					
		<tr>
			<td>
				$filter
			</td>
			<td>
									<a href="../../interfaces/Tenet/FilterInterface.md">FilterInterface</a>
				
			</td>
			<td>
				The filter implementation
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			void
		</dt>
		<dd>
			Provides no return value.
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">fill()</span>

Set all fields of an object with the supplied data

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
				$object
			</td>
			<td>
									object				
			</td>
			<td>
				The managed object to fill
			</td>
		</tr>
					
		<tr>
			<td>
				$data
			</td>
			<td>
									<a href="http://www.php.net/language.types.array.php">array</a>
				
			</td>
			<td>
				The data array to fill the object with
			</td>
		</tr>
					
		<tr>
			<td>
				$files
			</td>
			<td>
									<a href="http://www.php.net/language.types.array.php">array</a>
				
			</td>
			<td>
				The files array to fill the object with
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			object
		</dt>
		<dd>
			The object passed as the first argumnent
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">get()</span>

Get a single field on an object

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
				$object
			</td>
			<td>
									object				
			</td>
			<td>
				The object to set the field on
			</td>
		</tr>
					
		<tr>
			<td>
				$field
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The name of the field to get
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			mixed
		</dt>
		<dd>
			The filtered value currently set on the field
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">getFieldType()</span>

Gets the type of a field. Treats associations as types.

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
				$object
			</td>
			<td>
									object				
			</td>
			<td>
				The object
			</td>
		</tr>
					
		<tr>
			<td>
				$field
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The field to get the type of
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			string
		</dt>
		<dd>
			The ObjectManager's name of the type
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">getObjectManager()</span>

Get the default ObjectManager for a given class

###### Returns

<dl>
	
		<dt>
			Doctrine\Common\Persistence\ObjectManager
		</dt>
		<dd>
			The default object manager which manages the given class
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">set()</span>

Set a single field on an object

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
				$object
			</td>
			<td>
									object				
			</td>
			<td>
				The object to set the field on
			</td>
		</tr>
					
		<tr>
			<td>
				$field
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The name of the field to set
			</td>
		</tr>
					
		<tr>
			<td>
				$value
			</td>
			<td>
									<a href="http://www.php.net/language.pseudo-types.php">mixed</a>
				
			</td>
			<td>
				The value to filter and set on the object
			</td>
		</tr>
					
		<tr>
			<td>
				$update_related
			</td>
			<td>
									<a href="http://www.php.net/language.types.boolean.php">boolean</a>
				
			</td>
			<td>
				Whether or not related records should be updated
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			object
		</dt>
		<dd>
			The object passed as the first argument
		</dd>
	
</dl>


<hr />

#### <span style="color:#3e6a6e;">convert()</span>

Convert a value

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
				$object
			</td>
			<td>
									<a href="../../interfaces/Tenet/AccessInterface.md">AccessInterface</a>
				
			</td>
			<td>
				The object to convert for
			</td>
		</tr>
					
		<tr>
			<td>
				$field
			</td>
			<td>
									<a href="http://www.php.net/language.types.string.php">string</a>
				
			</td>
			<td>
				The field being converted
			</td>
		</tr>
					
		<tr>
			<td>
				$value
			</td>
			<td>
									<a href="http://www.php.net/language.pseudo-types.php">mixed</a>
				
			</td>
			<td>
				The value to convert
			</td>
		</tr>
					
		<tr>
			<td>
				$conversion
			</td>
			<td>
									<a href="http://www.php.net/language.types.integer.php">integer</a>
				
			</td>
			<td>
				The type of conversion to do. One of Accessor::SETTER, Accessor::GETTER
			</td>
		</tr>
			
	</tbody>
</table>

###### Returns

<dl>
	
		<dt>
			mixed
		</dt>
		<dd>
			The converted value
		</dd>
	
</dl>




