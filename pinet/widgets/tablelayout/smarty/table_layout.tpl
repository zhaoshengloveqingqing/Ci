{extends file='base_layout.tpl'}
{block name=head}
{css}
{/block}
{block name=body append}
  	{bs_container display=table}
		{row}
			{col id="layout-navigation"}
				{if $has_head}
					{block name=navigations}{** This is the location for navigations **}{/block}
				{/if}
			{/col}
			{col id="layout-main"}
				{table_container}
					{table_row id="layout-statebar"}
						{table_col}
							{if $has_head}
								{block name=statebar}{** The state bar of the layout **}{/block}
							{/if}
						{/table_col}
					{/table_row}
					{table_row id="layout-content"}
						{table_col}
							{table_container}
								{table_row}
									{table_col id="layout-workbench"}
										{table_container}
											{table_row id="layout-toolbar"}
												{table_col}
													{block name=toolbar}{** The state bar of the layout **}{/block}
												{/table_col}
											{/table_row}
											{table_row id="layout-messgaebar"}
												{table_col}
													{block name=messagebar}{** The state bar of the layout **}{/block}
												{/table_col}
											{/table_row}
											{table_row id="layout-scrollcontent"}
												{table_col}
													{div class="scroll-con"}
														{block name=workbench}{** The workbench of the layout **}{/block}
													{/div}
												{/table_col}
											{/table_row}
										{/table_container}
									{/table_col}
									{table_col id="layout-aside"}
										{if $has_head}
											{block name=aside}{** The side bar of the layout **}{/block}
										{/if}
									{/table_col}
								{/table_row}
							{/table_container}
						{/table_col}
					{/table_row}
				{/table_container}
			{/col}
		{/row}
  	{/bs_container}
{/block}
{block name=foot}
{js}
{init_js}
{/block}
