<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:careServicesRequest xmlns:csd="urn:ihe:iti:csd:2013" xmlns="urn:ihe:iti:csd:2013">             
      <function urn="urn:ihe:iti:csd:2014:stored-function:organization-search">
	<requestParams>                      
	  <xsl:if test="/form/field[@name='entityID']">
	    <id><xsl:attribute name='entityID'><xsl:value-of select="/form/field[@name='entityID']"/></xsl:attribute></id>
	  </xsl:if>
	  <xsl:if test="/form/field[@name='number'] and substring-after(/form/field[@name='csd_organization_otherid_type']/value,'@@@')">
	    <otherID>
	      <xsl:attribute name='code'><xsl:value-of select="substring-after(/form/field[@name='csd_organization_otherid_type']/value,'@@@')"/></xsl:attribute>
	      <xsl:if test="/form/field[@name='assigning_authority']">
		<xsl:attribute name='assigningAuthorityName'><xsl:value-of select="/form/field[@name='assigning_authority']"/></xsl:attribute>
	      </xsl:if>
	      <xsl:value-of select="/form/field[@name='number']"/>
	    </otherID>
	  </xsl:if>
	  <xsl:if test="/form/field[@name='primary_name']">
	    <primaryName><xsl:value-of select="/form/field[@name='primary_name']"/></primaryName>
	  </xsl:if>	  
	  <xsl:for-each select="/form/field[@name='csd_organization_type']/value[1]">
	    <codedType>
	      <xsl:attribute name='codeSystem'><xsl:value-of select="substring-before(text(),'@@@')"/></xsl:attribute>
	      <xsl:attribute name='code'><xsl:value-of select="substring-after(text(),'@@@')"/></xsl:attribute>
	    </codedType>
	  </xsl:for-each>	  
	  <address>
	    <xsl:for-each select="/form/field[@name='address']/value">
	      <xsl:if test="text()">
		<addressLine><xsl:attribute name='component'><xsl:value-of select="@keyid"/></xsl:attribute><xsl:value-of select="text()"/></addressLine>
	      </xsl:if>
	    </xsl:for-each>
	  </address>
	  <start><xsl:value-of select="/form/field[@name='start']"/></start>
	  <xsl:if test="/form/field[@name='max']">
	    <max><xsl:value-of select="/form/field[@name='max']"/></max>
	  </xsl:if>
	  <record>
	    <xsl:for-each select="/form/field[@name='csd_organization_status']/value[1]">
	     <xsl:attribute name='status'><xsl:value-of select="text()"/></xsl:attribute>
	    </xsl:for-each>
	    <xsl:for-each select="/form/field[@name='updated']/value[1]">
	     <xsl:attribute name='status'><xsl:value-of select="year"/>-<xsl:value-of select="month"/>-<xsl:value-of select="day"/>T<xsl:value-of select="hour"/>:<xsl:value-of select="minute"/>:<xsl:value-of select="second"/></xsl:attribute>
	    </xsl:for-each>
	  </record>
	</requestParams>             
      </function>         
    </csd:careServicesRequest>     
  </xsl:template> 
</xsl:stylesheet>
