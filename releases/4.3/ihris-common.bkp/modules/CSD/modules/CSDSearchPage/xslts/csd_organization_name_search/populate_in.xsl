<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:csd="urn:ihe:iti:csd:2013"
  exclude-result-prefixes="csd xsl">
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     

  <xsl:template match="/">
    <form name='csd_organization_name_search' id='0'>
      <field name='matches' type='ASSOC_MAP_RESULTS'>
	<xsl:for-each select="/csd:CSD/csd:organizationDirectory/csd:organization">
	  <value keyform='csd_organization' >
	    <xsl:attribute name='keyid'><xsl:value-of select="@entityID"/></xsl:attribute>
	    <xsl:value-of select="csd:primaryName/text()"/>
	  </value>
	</xsl:for-each>
      </field>
    </form>
  </xsl:template>         

</xsl:stylesheet>
